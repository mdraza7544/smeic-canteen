<div class="page-header">
    <div>
        <h1 class="page-title">Earnings</h1>
        <p class="page-subtitle">Revenue breakdown over time</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 mb-6">
    <div class="kpi-card kpi-emerald">
        <div class="kpi-label">Today</div>
        <div class="kpi-value"><?= e(money($today)) ?></div>
    </div>
    <div class="kpi-card kpi-blue">
        <div class="kpi-label">Last 7 Days</div>
        <div class="kpi-value"><?= e(money($week)) ?></div>
    </div>
    <div class="kpi-card kpi-indigo">
        <div class="kpi-label">This Month</div>
        <div class="kpi-value"><?= e(money($month)) ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">Last 30 Days Breakdown</div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Date</th><th class="text-right">Orders</th><th class="text-right">Amount</th></tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="3"><div class="empty-state">No data yet.</div></td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td class="font-medium text-slate-700"><?= e($r['d']) ?></td>
                    <td class="text-right"><span class="badge bg-indigo-100 text-indigo-700"><?= e($r['orders']) ?></span></td>
                    <td class="text-right font-semibold num text-emerald-700"><?= e(money((float)$r['amount'])) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
