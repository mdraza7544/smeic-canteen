<?php $pageTitle = $pageTitle ?? 'Change Password'; ?>
<div class="auth-page">
    <div class="auth-card">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <div class="text-base"><?= !empty($forced) ? 'Set your new password' : 'Change your password' ?></div>
                        <div class="text-xs text-slate-500 font-normal">Must be at least 6 characters</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 text-red-700 text-sm rounded-lg p-3 mb-4 border border-red-200"><?= e($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="bg-emerald-50 text-emerald-700 text-sm rounded-lg p-3 mb-4 border border-emerald-200"><?= e($success) ?></div>
                <?php endif; ?>
                <form method="post" class="space-y-4" autocomplete="off">
                    <?= csrf_field() ?>
                    <div>
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-input" minlength="6" required>
                    </div>
                    <div>
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-input" minlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-full !py-3 text-base">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
