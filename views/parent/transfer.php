<div class="page-header">
    <div>
        <h1 class="page-title">Transfer Funds</h1>
        <p class="page-subtitle">Send money to your child's wallet</p>
    </div>
    <a class="btn btn-secondary" href="<?= e(url('index.php?action=parent/dashboard')) ?>">← Dashboard</a>
</div>

<?php if (!empty($msg)): ?><div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800"><?= e($msg) ?></div><?php endif; ?>
<?php if (!empty($err)): ?><div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700"><?= e($err) ?></div><?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="md:col-span-1">
        <div class="kpi-card kpi-indigo h-full">
            <div class="kpi-label">Available Balance</div>
            <div class="kpi-value"><?= e(money((float)$parent['wallet_balance'])) ?></div>
            <div class="kpi-sub">Your wallet</div>
        </div>
    </div>
    <div class="md:col-span-2">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <span>New Transfer</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($children)): ?>
                    <p class="text-slate-500 text-sm">No children linked. Please contact the admin.</p>
                <?php else: ?>
                <form method="post" class="space-y-4">
                    <?= csrf_field() ?>
                    <div>
                        <label class="form-label">Child</label>
                        <select class="form-select" name="student_id" required>
                            <option value="">— Select child —</option>
                            <?php foreach ($children as $c): ?>
                                <option value="<?= e($c['id']) ?>"><?= e($c['student_id'] . ' — ' . $c['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Amount (<?= CURRENCY ?>)</label>
                        <input type="number" min="1" max="<?= e((float)$parent['wallet_balance']) ?>" step="0.01" class="form-input" name="amount" required placeholder="0.00">
                        <div class="form-hint">Maximum: <?= e(money((float)$parent['wallet_balance'])) ?></div>
                    </div>
                    <button class="btn btn-primary w-full !py-3 text-base">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Transfer Now
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
