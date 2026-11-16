<?php
$user = current_user();
$flashes = flash_get();
send_security_headers();
$notifHref = '';
if ($user) {
    if ($user['role'] === 'student') $notifHref = url('index.php?action=student/notifications');
    elseif ($user['role'] === 'parent') $notifHref = url('index.php?action=parent/notifications');
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?> &mdash; <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(url('assets/img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>
<body class="app-shell"<?= $user ? ' data-user-id="' . e($user['user_id']) . '" data-user-type="' . e($user['role']) . '"' : '' ?>>

<header class="topbar">
    <div class="topbar-inner">
        <div class="topbar-left">
            <button id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle menu">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="<?= e(url('index.php')) ?>" class="topbar-brand">
                <span class="logo-mark">SC</span>
                <span class="brand-text"><?= e(APP_NAME) ?></span>
            </a>
        </div>
        <div class="topbar-right">
            <?php if ($user): ?>
                <?php if ($notifHref): ?>
                    <a href="<?= e($notifHref) ?>" class="topbar-icon-btn" aria-label="Notifications" title="Notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span data-notif-badge class="hidden"></span>
                    </a>
                <?php endif; ?>
                <div class="user-chip">
                    <div class="avatar"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></div>
                    <div class="info">
                        <div class="name"><?= e($user['name']) ?></div>
                        <div class="role"><?= e($user['role']) ?></div>
                    </div>
                </div>
                <a href="<?= e(url('index.php?action=logout')) ?>" class="btn btn-ghost btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="logout-text">Logout</span>
                </a>
            <?php else: ?>
                <a href="<?= e(url('index.php?action=login')) ?>" class="btn btn-primary btn-sm">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if (!empty($flashes)): ?>
    <div id="flash-container" class="flash-container">
        <?php foreach ($flashes as $f): ?>
            <div class="flash-msg flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
        <?php endforeach; ?>
    </div>
    <script>
        setTimeout(()=>{ document.querySelectorAll('.flash-msg').forEach(el=>{ el.style.transition='opacity 0.4s'; el.style.opacity='0'; setTimeout(()=>el.remove(),400); }); }, 4500);
    </script>
<?php endif; ?>

<div class="app-main">
