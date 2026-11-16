<?php
// Run this once to clear the "must change password" flag on all seed accounts.
// Visit: http://localhost/SMEIC/public/clear_force_change.php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$pdo = db();
$pdo->exec("UPDATE users SET force_password_change=0");
$pdo->exec("UPDATE students SET force_password_change=0");
$pdo->exec("UPDATE parents SET force_password_change=0");
$pdo->exec("UPDATE canteen_staff SET force_password_change=0");

echo '<!doctype html><html><head><meta charset="utf-8"><title>Done</title>
<style>body{font-family:system-ui;background:#0f172a;color:#e2e8f0;padding:2rem;text-align:center}
.card{background:#1e293b;padding:2rem;border-radius:12px;max-width:480px;margin:2rem auto}
a{color:#fbbf24}</style></head><body>
<div class="card">
<h1>✅ Force-change flags cleared</h1>
<p>All seed accounts can now log in directly without being redirected to the change-password page.</p>
<p><a href="' . htmlspecialchars(BASE_URL . '/index.php?action=login') . '">→ Go to login</a></p>
</div></body></html>';
