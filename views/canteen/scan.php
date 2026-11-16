<div class="page-header">
    <div>
        <h1 class="page-title">Token Verification</h1>
        <p class="page-subtitle">Verify a student's pickup token</p>
    </div>
    <a class="btn btn-secondary" href="<?= e(url('index.php?action=canteen/orders')) ?>">← Back to orders</a>
</div>

<?php if (!empty($msg)): ?><div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3"><svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><div><?= e($msg) ?></div></div><?php endif; ?>
<?php if (!empty($err)): ?><div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-start gap-3"><svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><div><?= e($err) ?></div></div><?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <span>Verify &amp; Mark Picked Up</span>
            </div>
        </div>
        <div class="card-body">
            <p class="text-sm text-slate-600 mb-3">Enter the 6-digit pickup token shown on the student's token page.</p>
            <form method="post" class="space-y-4">
                <?= csrf_field() ?>
                <input class="form-input font-mono text-lg text-center tracking-[0.3em]" name="token" placeholder="000000" pattern="\d{6}" maxlength="6" inputmode="numeric" autocomplete="off" required>
                <button class="btn btn-primary w-full !py-3">Verify &amp; Mark Picked Up</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">How it works</div>
        <div class="card-body text-sm text-slate-600 space-y-2">
            <div class="flex gap-2"><span class="badge bg-indigo-100 text-indigo-700">1</span> Student receives notification when their order is ready.</div>
            <div class="flex gap-2"><span class="badge bg-indigo-100 text-indigo-700">2</span> Student opens <code class="bg-slate-100 px-1 rounded">My Token</code> page in their account.</div>
            <div class="flex gap-2"><span class="badge bg-indigo-100 text-indigo-700">3</span> They show the 6-digit token to the canteen staff.</div>
            <div class="flex gap-2"><span class="badge bg-indigo-100 text-indigo-700">4</span> Staff enters the 6-digit code above.</div>
            <div class="flex gap-2"><span class="badge bg-emerald-100 text-emerald-700">✓</span> Order is marked as picked up.</div>
            <div class="flex gap-2"><span class="badge bg-amber-100 text-amber-700">!</span> Tokens expire after <?= e(TOKEN_EXPIRY_HOURS) ?> hours. Rate-limited to <?= e(TOKEN_VERIFY_MAX_ATTEMPTS) ?> attempts per 15 min.</div>
        </div>
    </div>
</div>
