<?php $pageTitle = $pageTitle ?? 'Forgot Password'; ?>
<div class="auth-page">
    <div class="auth-card">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-pink-500 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-base">Forgot password</div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 text-red-700 text-sm rounded-lg p-3 mb-4 border border-red-200"><?= e($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($sent)): ?>
                    <div class="bg-emerald-50 text-emerald-800 text-sm rounded-lg p-3 mb-4 border border-emerald-200">
                        ✅ If an account exists, a reset link has been sent.
                    </div>
                <?php endif; ?>
                <form method="post" class="space-y-4" autocomplete="off">
                    <?= csrf_field() ?>
                    <div>
                        <label class="form-label">Your registered email</label>
                        <input type="email" name="email" class="form-input" required placeholder="you@example.com">
                    </div>
                    <button type="submit" class="btn btn-primary w-full !py-3">Send reset link</button>
                </form>
                <p class="text-xs text-slate-500 mt-3 text-center">In demo mode, reset emails are written to <code class="bg-slate-100 px-1 rounded">logs/mail.log</code></p>
                <p class="text-center mt-3"><a href="<?= e(url('index.php?action=login')) ?>" class="text-indigo-600 hover:underline text-sm">← Back to login</a></p>
            </div>
        </div>
    </div>
</div>
