<?php $pageTitle = $pageTitle ?? 'Reset Password'; ?>
<div class="auth-page">
    <div class="auth-card">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-indigo-500 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div class="text-base">Set a new password</div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 text-red-700 text-sm rounded-lg p-3 mb-4 border border-red-200"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="post" class="space-y-4" autocomplete="off">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <div>
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-input" minlength="6" required>
                    </div>
                    <div>
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-input" minlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-full !py-3">Reset password</button>
                </form>
            </div>
        </div>
    </div>
</div>
