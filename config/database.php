<?php
// PDO database connection (singleton). Configured for XAMPP defaults.

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host = '127.0.0.1';
        $dbname = 'smeic_canteen';
        $user = 'root';
        $pass = '';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            smeic_run_schema_migrations($pdo);
        } catch (PDOException $e) {
            // Log error and show a friendly message
            $logDir = dirname(__DIR__) . '/logs/';
            if (!is_dir($logDir)) { @mkdir($logDir, 0777, true); }
            error_log('[' . date('Y-m-d H:i:s') . '] DB connect error: ' . $e->getMessage() . PHP_EOL, 3, $logDir . 'error.log');
            http_response_code(500);
            echo '<h2>Database connection failed</h2><p>Please ensure MySQL is running and the <code>smeic_canteen</code> database has been imported from <code>database.sql</code>.</p>';
            exit;
        }
    }
    return $pdo;
}

function smeic_run_schema_migrations(PDO $pdo): void
{
    static $ran = false;
    if ($ran) {
        return;
    }
    $ran = true;

    $checks = [];
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM canteen_staff LIKE 'username'");
        $checks['canteen_staff_username'] = (bool)$stmt->fetch();
    } catch (Throwable $e) {
        $checks['canteen_staff_username'] = false;
    }
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM canteen_staff LIKE 'is_active'");
        $checks['canteen_staff_active'] = (bool)$stmt->fetch();
    } catch (Throwable $e) {
        $checks['canteen_staff_active'] = false;
    }
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM canteen_staff LIKE 'can_verify_tokens'");
        $checks['canteen_staff_permissions'] = (bool)$stmt->fetch();
    } catch (Throwable $e) {
        $checks['canteen_staff_permissions'] = false;
    }
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'staff_activity_log'");
        $checks['staff_activity_log'] = (bool)$stmt->fetch();
    } catch (Throwable $e) {
        $checks['staff_activity_log'] = false;
    }

    if (!$checks['canteen_staff_username']) {
        $pdo->exec("ALTER TABLE canteen_staff ADD COLUMN username VARCHAR(50) DEFAULT NULL AFTER phone");
        $pdo->exec("ALTER TABLE canteen_staff ADD UNIQUE KEY uniq_canteen_staff_username (username)");
    }
    if (!$checks['canteen_staff_active']) {
        $pdo->exec("ALTER TABLE canteen_staff ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER force_password_change");
    }
    if (!$checks['canteen_staff_permissions']) {
        $pdo->exec("ALTER TABLE canteen_staff ADD COLUMN role ENUM('manager','staff') NOT NULL DEFAULT 'staff' AFTER is_active");
        $pdo->exec("ALTER TABLE canteen_staff ADD COLUMN can_verify_tokens TINYINT(1) NOT NULL DEFAULT 1 AFTER role");
        $pdo->exec("ALTER TABLE canteen_staff ADD COLUMN can_complete_orders TINYINT(1) NOT NULL DEFAULT 1 AFTER can_verify_tokens");
        $pdo->exec("ALTER TABLE canteen_staff ADD COLUMN can_view_pending_orders TINYINT(1) NOT NULL DEFAULT 1 AFTER can_complete_orders");
    }
    if (!$checks['staff_activity_log']) {
        $pdo->exec("CREATE TABLE staff_activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT NOT NULL,
            action VARCHAR(120) NOT NULL,
            details TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_staff_log_staff (staff_id),
            INDEX idx_staff_log_created (created_at)
        ) ENGINE=InnoDB");
    }

    // Token verification log table
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'token_verification_log'");
        $checks['token_verification_log'] = (bool)$stmt->fetch();
    } catch (Throwable $e) {
        $checks['token_verification_log'] = false;
    }
    if (!$checks['token_verification_log']) {
        $pdo->exec("CREATE TABLE token_verification_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT DEFAULT NULL,
            token VARCHAR(20) NOT NULL,
            success TINYINT(1) NOT NULL DEFAULT 0,
            staff_id INT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tvlog_order (order_id),
            INDEX idx_tvlog_token (token),
            INDEX idx_tvlog_created (created_at)
        ) ENGINE=InnoDB");
    }

    // Token generated_at column on orders
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'token_generated_at'");
        $checks['orders_token_generated_at'] = (bool)$stmt->fetch();
    } catch (Throwable $e) {
        $checks['orders_token_generated_at'] = false;
    }
    if (!$checks['orders_token_generated_at']) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN token_generated_at DATETIME DEFAULT NULL AFTER pickup_token");
    }

    try {
        $pdo->prepare("UPDATE canteen_staff SET username = COALESCE(username, email), is_active = COALESCE(is_active, 1), role = COALESCE(role, 'staff'), can_verify_tokens = COALESCE(can_verify_tokens, 1), can_complete_orders = COALESCE(can_complete_orders, 1), can_view_pending_orders = COALESCE(can_view_pending_orders, 1)")->execute();
    } catch (Throwable $e) {
        // ignore schema backfill issues on older installations
    }
}
