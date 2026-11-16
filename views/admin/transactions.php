<div class="page-header">
    <div>
        <h1 class="page-title">Transactions</h1>
        <p class="page-subtitle">All wallet activity across the system</p>
    </div>
    <div class="flex gap-2">
        <a class="btn btn-secondary btn-sm" href="<?= e(url('index.php?action=admin/export&kind=transactions&format=csv')) ?>">CSV</a>
        <a class="btn btn-primary btn-sm" href="<?= e(url('index.php?action=admin/export&kind=transactions&format=pdf')) ?>">PDF</a>
    </div>
</div>

<form method="get" class="card mb-4">
    <input type="hidden" name="action" value="admin/transactions">
    <div class="card-body grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div><label class="form-label">From</label><input type="date" class="form-input" name="from" value="<?= e($from) ?>"></div>
        <div><label class="form-label">To</label><input type="date" class="form-input" name="to" value="<?= e($to) ?>"></div>
        <div>
            <label class="form-label">Type</label>
            <select class="form-select" name="type">
                <option value="">All</option>
                <option value="credit" <?= $type==='credit'?'selected':'' ?>>Credit</option>
                <option value="debit" <?= $type==='debit'?'selected':'' ?>>Debit</option>
            </select>
        </div>
        <div>
            <label class="form-label">User Type</label>
            <select class="form-select" name="ut">
                <option value="">All</option>
                <option value="parent" <?= $ut==='parent'?'selected':'' ?>>Parent</option>
                <option value="student" <?= $ut==='student'?'selected':'' ?>>Student</option>
                <option value="admin" <?= $ut==='admin'?'selected':'' ?>>Admin</option>
            </select>
        </div>
        <button class="btn btn-secondary">Filter</button>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>When</th><th>User</th><th>Type</th><th class="text-right">Amount</th><th>Reference</th><th class="text-right">Balance</th></tr></thead>
            <tbody>
            <?php if (empty($tx)): ?>
                <tr><td colspan="6"><div class="empty-state">No transactions found.</div></td></tr>
            <?php else: foreach ($tx as $t): ?>
                <tr>
                    <td class="text-slate-500 text-xs whitespace-nowrap"><?= e(dt_fmt($t['created_at'])) ?></td>
                    <td>
                        <div class="font-medium text-slate-800"><?= e($t['user_name'] ?? '-') ?></div>
                        <div class="text-xs text-slate-400"><?= e($t['user_type']) ?></div>
                    </td>
                    <td><span class="badge badge-<?= e($t['type']) ?>"><?= e(strtoupper($t['type'])) ?></span></td>
                    <td class="text-right font-semibold num"><?= e(money((float)$t['amount'])) ?></td>
                    <td class="text-xs text-slate-500"><?= e($t['reference']) ?></td>
                    <td class="text-right text-slate-600 num"><?= e(money((float)($t['balance_after'] ?? 0))) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
