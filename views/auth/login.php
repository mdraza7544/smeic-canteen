<?php $pageTitle = $pageTitle ?? 'Login'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> &mdash; <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(url('assets/img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>
<body class="login-page">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-fuchsia-400 rounded-full mix-blend-overlay filter blur-3xl opacity-30 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-300 rounded-full mix-blend-overlay filter blur-3xl opacity-30"></div>
    </div>

    <div class="relative w-full max-w-md">
        <div class="text-center mb-6">
            <div class="inline-flex login-brand mb-3">SC</div>
            <h1 class="text-white text-3xl font-extrabold tracking-tight"><?= e(APP_NAME) ?></h1>
            <p class="text-indigo-100 text-sm mt-1">Sign in to continue to your dashboard</p>
        </div>

        <div class="login-card">
            <div class="role-tabs">
                <?php $r = $_GET['role'] ?? $_POST['role'] ?? ''; ?>
                <a href="?action=login&role=admin"   class="role-tab <?= $r==='admin'?'active':'' ?>">Admin</a>
                <a href="?action=login&role=student" class="role-tab <?= $r==='student'?'active':'' ?>">Student</a>
                <a href="?action=login&role=parent"  class="role-tab <?= $r==='parent'?'active':'' ?>">Parent</a>
                <a href="?action=login&role=canteen" class="role-tab <?= $r==='canteen'?'active':'' ?>">Canteen</a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-700 text-sm rounded-lg p-3 mb-4 border border-red-200 flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($needsHashReset)): ?>
                <div class="bg-amber-50 text-amber-800 text-sm rounded-lg p-3 mb-4 border border-amber-300">
                    ⚠️ Seeded password hashes look like placeholders.
                    <a href="<?= e(url('generate_hashes.php')) ?>" class="font-semibold underline">Click here to fix in one click</a>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-4" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="role" value="<?= e($r ?: 'student') ?>">

                <div>
                    <label class="form-label"><?= $r === 'student' ? 'Student ID' : ($r === 'parent' ? 'Parent ID or Email' : 'Email Address') ?></label>
                    <input type="text" name="identifier" class="form-input" required value="<?= e($identifier ?? '') ?>"
                           placeholder="<?= $r==='student'?'S10001':($r==='parent'?'P10001 or email':'admin@canteen.com') ?>">
                </div>
                <div>
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary w-full !py-3 text-base">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Sign in
                </button>
            </form>

            <div class="text-center mt-5">
                <a href="<?= e(url('index.php?action=forgot-password')) ?>" class="text-sm text-indigo-600 hover:underline">Forgot password?</a>
            </div>
        </div>

        <div class="demo-creds mt-5">
            <div class="font-semibold mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Demo credentials
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1">
                <div>Admin: <code>admin@canteen.com</code></div>
                <div>Pass: <code>Admin@123</code></div>
                <div>Student: <code>S10001</code></div>
                <div>Pass: <code>Student@123</code></div>
                <div>Parent: <code>P10001</code></div>
                <div>Pass: <code>Parent@123</code></div>
                <div>Canteen: <code>canteen@canteen.com</code></div>
                <div>Pass: <code>Canteen@123</code></div>
            </div>
        </div>
    </div>

    <script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>
