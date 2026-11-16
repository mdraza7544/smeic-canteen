<?php
// One-time password hash reset for seeded accounts.
// Visit: http://localhost/SMEIC/public/generate_hashes.php
// SAFE: only updates the seed accounts; leaves any other rows untouched.
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

ini_set('display_errors', '1');
error_reporting(E_ALL);

$messages = [];
$errors   = [];

try {
    $pdo = db();
} catch (Throwable $e) {
    $errors[] = 'Database connection failed: ' . $e->getMessage();
}

if (!$errors) {
    // 1) Admin user
    $hash = password_hash('Admin@123', PASSWORD_BCRYPT);
    $r = $pdo->prepare("UPDATE users SET password=?, force_password_change=1 WHERE email=? AND role='admin'");
    $r->execute([$hash, 'admin@canteen.com']);
    $messages[] = "admin@canteen.com / Admin@123  (rows: " . $r->rowCount() . ")";

    // 2) Canteen user (in users + canteen_staff)
    $hash = password_hash('Canteen@123', PASSWORD_BCRYPT);
    $r = $pdo->prepare("UPDATE users SET password=?, force_password_change=1 WHERE email=? AND role='canteen'");
    $r->execute([$hash, 'canteen@canteen.com']);
    $messages[] = "users.canteen@canteen.com / Canteen@123  (rows: " . $r->rowCount() . ")";

    $r = $pdo->prepare("UPDATE canteen_staff SET password=?, force_password_change=1 WHERE email=?");
    $r->execute([$hash, 'canteen@canteen.com']);
    $messages[] = "canteen_staff.canteen@canteen.com / Canteen@123  (rows: " . $r->rowCount() . ")";

    // 3) Parents (parent1@school.com / parent2@school.com)
    $parentMap = [
        ['P10001', 'parent1@school.com', 'Parent@123'],
        ['P10002', 'parent2@school.com', 'Parent@123'],
    ];
    foreach ($parentMap as [$pid, $email, $pwd]) {
        $hash = password_hash($pwd, PASSWORD_BCRYPT);
        $r = $pdo->prepare("UPDATE parents SET password=?, force_password_change=1 WHERE parent_id=?");
        $r->execute([$hash, $pid]);
        $messages[] = "parents.$pid  (rows: " . $r->rowCount() . ")";
        $r = $pdo->prepare("UPDATE users SET password=?, force_password_change=1 WHERE email=? AND role='parent'");
        $r->execute([$hash, $email]);
        $messages[] = "users.$email  (rows: " . $r->rowCount() . ")";
    }

    // 4) Students
    $studentMap = [
        ['S10001', 'Student@123'],
        ['S10002', 'Student@123'],
    ];
    foreach ($studentMap as [$sid, $pwd]) {
        $hash = password_hash($pwd, PASSWORD_BCRYPT);
        $r = $pdo->prepare("UPDATE students SET password=?, force_password_change=1 WHERE student_id=?");
        $r->execute([$hash, $sid]);
        $messages[] = "students.$sid / Student@123  (rows: " . $r->rowCount() . ")";
    }
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SMEIC password reset</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
        .card { background: #1e293b; padding: 1.5rem 2rem; border-radius: 12px; max-width: 720px; margin: 0 auto; }
        h1 { color: #fff; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #fbbf24; }
        ul { line-height: 1.9; }
        .ok { color: #4ade80; }
        .err { color: #f87171; }
        a.btn { display: inline-block; margin-top: 1rem; background: #4f46e5; color: #fff; padding: 8px 18px; border-radius: 6px; text-decoration: none; }
    </style>
</head>
<body>
<div class="card">
    <h1>🔐 SMEIC — Password hash reset</h1>
    <?php if (!empty($errors)): ?>
        <p class="err">❌ Errors:</p>
        <ul class="err"><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul>
    <?php endif; ?>

    <?php if (!empty($messages)): ?>
        <p class="ok">✅ Done. The following rows were updated:</p>
        <ul>
            <?php foreach ($messages as $m) echo '<li>' . $m . '</li>'; ?>
        </ul>
        <h2>Login credentials</h2>
        <ul>
            <li><code>admin@canteen.com</code> / <code>Admin@123</code></li>
            <li><code>S10001</code> or <code>S10002</code> / <code>Student@123</code></li>
            <li><code>P10001</code> or <code>P10002</code> / <code>Parent@123</code></li>
            <li><code>canteen@canteen.com</code> / <code>Canteen@123</code></li>
        </ul>
        <a class="btn" href="<?= htmlspecialchars(BASE_URL . '/index.php?action=login') ?>">→ Go to login</a>
    <?php endif; ?>
</div>
</body>
</html>
