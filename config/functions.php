<?php
// Global helper functions: escaping, sessions, CSRF, auth, redirects.

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $isSecure,
    ]);
    session_start();
}

/** Send security headers */
function send_security_headers(): void
{
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob: https:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
}

/** Escape output */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Redirect helper */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Generate URL relative to BASE_URL */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** CSRF token (per session) */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Emit hidden input for CSRF */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Validate CSRF token */
function csrf_check(): bool
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/** Flash messages */
function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): array
{
    $msgs = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $msgs;
}

/** Get current logged-in user info */
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) return null;
    return [
        'user_id'  => $_SESSION['user_id'],
        'role'     => $_SESSION['role'] ?? null,
        'name'     => $_SESSION['name'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'force_change' => $_SESSION['force_change'] ?? 0,
    ];
}

/** Require login (any role) */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        flash_set('error', 'Please log in to continue.');
        redirect(url('index.php?action=login'));
    }
}

/** Require specific role */
function require_role(array $roles): void
{
    require_login();
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        flash_set('error', 'Unauthorized access.');
        redirect(url('index.php?action=login'));
    }
}

/** Sanitize a string */
function clean_str($v): string
{
    return trim(filter_var((string)$v, FILTER_SANITIZE_SPECIAL_CHARS));
}

/** Generate next student/parent ID like S10003 or P10005 */
function generate_id(string $prefix, string $table, string $column): string
{
    $pdo = db();
    $stmt = $pdo->prepare("SELECT $column FROM $table WHERE $column LIKE ? ORDER BY $column DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    $num = 10000;
    if ($last && preg_match('/(\d+)$/', $last, $m)) {
        $num = (int)$m[1];
    }
    return $prefix . str_pad((string)($num + 1), 5, '0', STR_PAD_LEFT);
}

/** Generate random password */
function random_password(int $len = 10): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#';
    $max = strlen($alphabet) - 1;
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $out .= $alphabet[random_int(0, $max)];
    }
    return $out;
}

/** Format currency */
function money($amount): string
{
    return CURRENCY . number_format((float)$amount, 2);
}

/** Pretty date/time */
function dt_fmt(?string $val, string $fmt = 'd M Y, h:i A'): string
{
    if (!$val) return '-';
    $ts = strtotime($val);
    return $ts ? date($fmt, $ts) : '-';
}

/** Log a message */
function app_log(string $msg): void
{
    if (!is_dir(LOG_PATH)) { @mkdir(LOG_PATH, 0777, true); }
    error_log('[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, 3, LOG_PATH . 'app.log');
}

/** Audit log entry */
function audit(int $adminId, string $action, string $details = ''): void
{
    try {
        $stmt = db()->prepare("INSERT INTO audit_logs (admin_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$adminId, $action, $details]);
    } catch (Throwable $e) {
        app_log('Audit log failed: ' . $e->getMessage());
    }
}

/** Render a view file with given variables */
function view(string $path, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $full = dirname(__DIR__) . '/views/' . ltrim($path, '/') . '.php';
    if (!is_file($full)) {
        http_response_code(404);
        echo '<h2>View not found: ' . e($path) . '</h2>';
        return;
    }
    require $full;
}

/** Render a view inside a layout (with header/footer) */
function render_with_layout(string $viewPath, array $data = [], string $layout = 'main'): void
{
    $data['__view__'] = $viewPath;
    $data['__layout__'] = $layout;
    extract($data, EXTR_SKIP);

    // Header
    $layoutHeader = dirname(__DIR__) . '/views/layouts/header.php';
    if (is_file($layoutHeader)) require $layoutHeader;

    // Sidebar
    $layoutSidebar = dirname(__DIR__) . '/views/layouts/sidebar.php';
    if (is_file($layoutSidebar)) require $layoutSidebar;

    // Main view
    $viewFile = dirname(__DIR__) . '/views/' . ltrim($viewPath, '/') . '.php';
    if (is_file($viewFile)) {
        require $viewFile;
    } else {
        echo '<div class="p-6"><h2>View not found</h2></div>';
    }

    // Footer
    $layoutFooter = dirname(__DIR__) . '/views/layouts/footer.php';
    if (is_file($layoutFooter)) require $layoutFooter;
}

/** JSON response helper */
function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Read JSON body */
function json_input(): array
{
    $body = file_get_contents('php://input');
    if (!$body) return [];
    $data = json_decode($body, true);
    return is_array($data) ? $data : [];
}

/** Wallet helpers (atomic) */
function get_student_balance(int $studentId): float
{
    $stmt = db()->prepare("SELECT balance FROM wallets WHERE student_id = ?");
    $stmt->execute([$studentId]);
    return (float)($stmt->fetchColumn() ?: 0);
}

function get_parent_balance(int $parentId): float
{
    $stmt = db()->prepare("SELECT wallet_balance FROM parents WHERE id = ?");
    $stmt->execute([$parentId]);
    return (float)($stmt->fetchColumn() ?: 0);
}

/** Push a notification to a user (student or parent or canteen) */
function notify(int $userId, string $userType, string $title, string $message): void
{
    try {
        $stmt = db()->prepare("INSERT INTO notifications (user_id, user_type, title, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$userId, $userType, $title, $message]);
    } catch (Throwable $e) {
        app_log('notify() failed: ' . $e->getMessage());
    }
}

/** Pickup reminder trigger (called by polling endpoints) */
function send_pickup_reminder(int $orderId): bool
{
    $pdo = db();
    $stmt = $pdo->prepare("SELECT o.*, s.id AS s_id, s.name AS s_name FROM orders o JOIN students s ON s.id = o.student_id WHERE o.id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order) return false;
    if ($order['status'] !== 'ready') return false;
    if ((int)$order['pickup_reminder_count'] >= PICKUP_REMINDER_MAX) return false;

    // Increment counter
    $upd = $pdo->prepare("UPDATE orders SET pickup_reminder_count = pickup_reminder_count + 1 WHERE id = ?");
    $upd->execute([$orderId]);

    $count = (int)$order['pickup_reminder_count'] + 1;
    notify(
        (int)$order['s_id'],
        'student',
        'Pickup Reminder #' . $count,
        'Your order ' . $order['order_number'] . ' is ready. Please pick it up from the canteen.'
    );
    return true;
}

/** Dispatch pickup reminders for all eligible ready orders */
function dispatch_pickup_reminders(): int
{
    $pdo = db();
    $sql = "SELECT id FROM orders
            WHERE status = 'ready'
              AND ready_at IS NOT NULL
              AND TIMESTAMPDIFF(SECOND, ready_at, NOW()) >= ?
              AND pickup_reminder_count < ?
            ORDER BY ready_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([PICKUP_REMINDER_AFTER, PICKUP_REMINDER_MAX]);
    $count = 0;
    while ($row = $stmt->fetch()) {
        if (send_pickup_reminder((int)$row['id'])) $count++;
    }
    return $count;
}

/** Get active daily special */
function get_active_special(): ?array
{
    $stmt = db()->prepare("SELECT ds.*, mi.name, mi.description, mi.price, mi.image, mi.category
                            FROM daily_specials ds
                            JOIN menu_items mi ON mi.id = ds.menu_item_id
                            WHERE ds.is_active = 1 AND ds.expires_at > NOW()
                            ORDER BY ds.special_date DESC LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Menu image URL helper */
function menu_image(?string $filename): string
{
    if (!$filename) return url('assets/img/placeholder.png');
    $path = MENU_UPLOAD_PATH . $filename;
    if (is_file($path)) return MENU_UPLOAD_URL . rawurlencode($filename);
    return url('assets/img/placeholder.png');
}

/** Generate a cryptographically secure 6-digit pickup token */
function generate_pickup_token(): string
{
    $min = (int)str_repeat('1', TOKEN_LENGTH);
    $max = (int)str_repeat('9', TOKEN_LENGTH);
    return (string)random_int($min, $max);
}

/** Check if a pickup token is expired */
function is_token_expired(?string $tokenGeneratedAt): bool
{
    if (!$tokenGeneratedAt) return false;
    $generated = strtotime($tokenGeneratedAt);
    return ($generated + (TOKEN_EXPIRY_HOURS * 3600)) < time();
}

/** Rate-limit check for token verification */
function check_token_rate_limit(): array
{
    $key = 'token_verify_attempts';
    $timeKey = 'token_verify_first_attempt';
    $attempts = (int)($_SESSION[$key] ?? 0);
    $firstAttempt = (int)($_SESSION[$timeKey] ?? 0);

    if ($firstAttempt > 0 && (time() - $firstAttempt) > TOKEN_VERIFY_LOCKOUT_SECONDS) {
        $_SESSION[$key] = 0;
        $_SESSION[$timeKey] = 0;
        $attempts = 0;
    }

    return [
        'allowed' => $attempts < TOKEN_VERIFY_MAX_ATTEMPTS,
        'remaining' => max(0, TOKEN_VERIFY_MAX_ATTEMPTS - $attempts),
    ];
}

/** Increment token verification attempt counter */
function increment_token_rate_limit(): void
{
    $key = 'token_verify_attempts';
    $timeKey = 'token_verify_first_attempt';
    if (empty($_SESSION[$timeKey])) {
        $_SESSION[$timeKey] = time();
    }
    $_SESSION[$key] = (int)($_SESSION[$key] ?? 0) + 1;
}

/** Reset token verification rate limit after success */
function reset_token_rate_limit(): void
{
    unset($_SESSION['token_verify_attempts'], $_SESSION['token_verify_first_attempt']);
}

/** Log token verification attempt */
function log_token_verification(int $orderId, string $token, bool $success, ?int $staffId = null, string $ip = ''): void
{
    try {
        $pdo = db();
        $pdo->prepare("INSERT INTO token_verification_log (order_id, token, success, staff_id, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())")
            ->execute([$orderId, $token, $success ? 1 : 0, $staffId, $ip ?: ($_SERVER['REMOTE_ADDR'] ?? '')]);
    } catch (Throwable $e) {
        app_log('token verification log failed: ' . $e->getMessage());
    }
}
