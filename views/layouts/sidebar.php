<?php
$user = current_user();
$role = $user['role'] ?? '';
$current = $_GET['action'] ?? '';

$settingsExpanded = [
    'student' => ['student/settings/profile', 'student/settings/password', 'student/settings/track-order', 'student/settings/transactions', 'student/settings/token'],
    'parent'  => ['parent/settings/profile', 'parent/settings/notifications', 'parent/settings/password'],
    'canteen' => ['canteen/settings/profile', 'canteen/settings/staff', 'canteen/settings/password'],
    'admin'   => ['admin/settings/topup', 'admin/settings/transactions', 'admin/settings/reports', 'admin/settings/audit', 'admin/settings/password'],
];

$settingsOpen = false;
foreach ($settingsExpanded[$role] ?? [] as $needle) {
    if (str_starts_with($current, $needle)) { $settingsOpen = true; break; }
}

$menus = [
    'admin' => [
        ['admin/dashboard', 'Dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['admin/students', 'Students', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['admin/parents', 'Parents', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        ['admin/orders', 'Orders', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ],
    'student' => [
        ['student/dashboard', 'Dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['student/menu', 'Menu', 'M4 6h16M4 12h16M4 18h7'],
        ['student/cart', 'Cart', 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
        ['student/orders', 'My Orders', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ],
    'parent' => [
        ['parent/dashboard', 'Dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['parent/transfer', 'Transfer Funds', 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
        ['parent/menu', 'Browse Menu', 'M4 6h16M4 12h16M4 18h7'],
        ['parent/preferences', 'Preferences', 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
        ['parent/child-orders', 'Child Orders', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ],
    'canteen' => [
        ['canteen/dashboard', 'Dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['canteen/orders', 'Orders', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['canteen/token-verification', 'Token Verification', 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'],
        ['canteen/menu', 'Menu Items', 'M4 6h16M4 12h16M4 18h7'],
        ['canteen/daily-special', 'Daily Specials', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.36-2.448a1 1 0 00-1.175 0l-3.371 2.448c-.784.57-1.838-.197-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z'],
    ],
];

$sidebarItems = $menus[$role] ?? [];
?>
<aside id="sidebar" class="sidebar">
    <nav>
        <?php foreach ($sidebarItems as $it):
            $href = url('index.php?action=' . $it[0]);
            $active = str_starts_with($current, $it[0]) || ($current === '' && $it[0] === ($role . '/dashboard'));
        ?>
            <a href="<?= e($href) ?>" class="sidebar-link <?= $active ? 'active' : '' ?>">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= e($it[2]) ?>"/></svg>
                <span class="link-label"><?= e($it[1]) ?></span>
            </a>
        <?php endforeach; ?>

        <?php
        $settingsLinks = [
            'admin' => [
                ['admin/settings/topup', 'Wallet Top-up'],
                ['admin/settings/transactions', 'Transactions'],
                ['admin/settings/reports', 'Reports'],
                ['admin/settings/audit', 'Audit Log'],
                ['admin/settings/password', 'Change Password'],
            ],
            'student' => [
                ['student/settings/profile', 'Profile'],
                ['student/settings/password', 'Change Password'],
                ['student/settings/track-order', 'Track Order'],
                ['student/settings/transactions', 'Transactions'],
                ['student/settings/token', 'My Token'],
            ],
            'parent' => [
                ['parent/settings/profile', 'Profile'],
                ['parent/settings/notifications', 'Notifications'],
                ['parent/settings/password', 'Change Password'],
            ],
            'canteen' => [
                ['canteen/settings/profile', 'Profile'],
                ['canteen/settings/staff', 'Staff Management'],
                ['canteen/settings/password', 'Change Password'],
            ],
        ];
        $settingsId = 'settings-group-' . $role;
        ?>
        <div class="sidebar-settings <?= $settingsOpen ? 'open' : '' ?>" data-settings-group>
            <button type="button" class="sidebar-settings-toggle" aria-expanded="<?= $settingsOpen ? 'true' : 'false' ?>" aria-controls="<?= e($settingsId) ?>">
                <span class="sidebar-settings-left">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317a1.75 1.75 0 012.35 0l.71.609a1.75 1.75 0 002.43 0l.71-.61a1.75 1.75 0 012.35 0l1.003 1.004a1.75 1.75 0 010 2.475l-.61.71a1.75 1.75 0 000 2.43l.61.71a1.75 1.75 0 010 2.475l-1.003 1.004a1.75 1.75 0 01-2.35 0l-.71-.61a1.75 1.75 0 00-2.43 0l-.71.61a1.75 1.75 0 01-2.35 0l-1.003-1.004a1.75 1.75 0 010-2.475l.61-.71a1.75 1.75 0 000-2.43l-.61-.71a1.75 1.75 0 010-2.475l1.003-1.004zM12 15.25a3.25 3.25 0 100-6.5 3.25 3.25 0 000 6.5z"/></svg>
                    <span class="link-label">Settings</span>
                </span>
                <svg class="sidebar-chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
            </button>
            <div id="<?= e($settingsId) ?>" class="sidebar-settings-panel" role="region" aria-label="Settings submenu">
                <?php foreach ($settingsLinks[$role] ?? [] as $link):
                    $isActive = str_starts_with($current, $link[0]);
                ?>
                    <a href="<?= e(url('index.php?action=' . $link[0])) ?>" class="sidebar-sublink <?= $isActive ? 'active' : '' ?>"><?= e($link[1]) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>
</aside>

<main class="app-content">
