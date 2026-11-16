<div class="page-header">
    <div>
        <h1 class="page-title">Wallet Top-up</h1>
        <p class="page-subtitle">Add funds to a parent or student wallet</p>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="mb-4 p-4 rounded-xl <?= str_contains($msg,'failed')||str_contains($msg,'required')||str_contains($msg,'must')||str_contains($msg,'Choose')||str_contains($msg,'Select')||str_contains($msg,'Invalid') ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' ?> flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= str_contains($msg,'failed')||str_contains($msg,'required') ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' ?>"/></svg>
        <div><?= e($msg) ?></div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-indigo-500 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span>Top-up Parent Wallet</span>
            </div>
        </div>
        <div class="card-body">
            <form method="post" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="target" value="parent">
                <div>
                    <label class="form-label">Select Parent</label>
                    <select class="form-select" name="parent_id" required>
                        <option value="">— Choose parent —</option>
                        <?php foreach ($parents as $p): ?>
                            <option value="<?= e($p['id']) ?>"><?= e($p['parent_id'] . ' — ' . $p['full_name']) ?> (Bal: <?= e(money((float)$p['wallet_balance'])) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Amount (<?= CURRENCY ?>)</label>
                    <input type="number" min="1" step="0.01" class="form-input" name="amount" required placeholder="0.00">
                </div>
                <div>
                    <label class="form-label">Note</label>
                    <input class="form-input" name="note" value="Wallet top-up by admin">
                </div>
                <button class="btn btn-primary w-full !py-3">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Add to Parent Wallet
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-fuchsia-500 to-indigo-500 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span>Top-up Student Wallet</span>
            </div>
        </div>
        <div class="card-body">
            <form method="post" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="target" value="student">
                <div>
                    <label class="form-label">Select Student</label>
                    <select class="form-select" name="student_id" required>
                        <option value="">— Choose student —</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= e($s['id']) ?>"><?= e($s['student_id'] . ' — ' . $s['full_name']) ?> (Bal: <?= e(money((float)$s['balance'])) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Amount (<?= CURRENCY ?>)</label>
                    <input type="number" min="1" step="0.01" class="form-input" name="amount" required placeholder="0.00">
                </div>
                <div>
                    <label class="form-label">Note</label>
                    <input class="form-input" name="note" value="Wallet top-up by admin">
                </div>
                <button class="btn btn-primary w-full !py-3">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Add to Student Wallet
                </button>
            </form>
        </div>
    </div>
</div>
