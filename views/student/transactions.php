<div class="page-header">
    <div>
        <h1 class="page-title">My Transactions</h1>
        <p class="page-subtitle">All wallet activity on your account</p>
    </div>
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>When</th><th>Type</th><th class="text-right">Amount</th><th>Reference</th><th class="text-right">Balance</th></tr></thead>
            <tbody>
            <?php if (empty($tx)): ?>
                <tr><td colspan="5"><div class="empty-state">No transactions.</div></td></tr>
            <?php else: foreach ($tx as $t): ?>
                <tr>
                    <td class="text-slate-500 text-xs whitespace-nowrap"><?= e(dt_fmt($t['created_at'])) ?></td>
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
