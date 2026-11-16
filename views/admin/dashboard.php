<div class="page-header">
    <div>
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">Real-time overview of your canteen operations</p>
    </div>
    <div class="flex gap-2">
        <a href="<?= e(url('index.php?action=admin/orders')) ?>" class="btn btn-secondary btn-sm">View Orders</a>
        <a href="<?= e(url('index.php?action=admin/reports')) ?>" class="btn btn-primary btn-sm">Export Reports</a>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="kpi-card kpi-indigo">
        <div class="kpi-label">Total Students</div>
        <div class="kpi-value"><?= e(number_format($totalStudents)) ?></div>
        <div class="kpi-sub">Active accounts</div>
    </div>
    <div class="kpi-card kpi-blue">
        <div class="kpi-label">Orders Today</div>
        <div class="kpi-value"><?= e(number_format($ordersToday)) ?></div>
        <div class="kpi-sub">All statuses</div>
    </div>
    <div class="kpi-card kpi-emerald">
        <div class="kpi-label">Revenue (Month)</div>
        <div class="kpi-value"><?= e(money($revenueMonth)) ?></div>
        <div class="kpi-sub">All time: <?= e(money($revenueAllTime)) ?></div>
    </div>
    <div class="kpi-card kpi-amber">
        <div class="kpi-label">Pending Orders</div>
        <div class="kpi-value"><?= e(number_format($pendingOrders)) ?></div>
        <div class="kpi-sub">Awaiting acceptance</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="card">
        <div class="card-header">
            <span>Weekly Orders</span>
            <span class="text-xs text-slate-500 font-normal">Last 7 days</span>
        </div>
        <div class="card-body"><canvas id="weeklyChart" height="200"></canvas></div>
    </div>
    <div class="card">
        <div class="card-header">
            <span>Revenue Trend</span>
            <span class="text-xs text-slate-500 font-normal">Last 6 months</span>
        </div>
        <div class="card-body"><canvas id="revenueChart" height="200"></canvas></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span>Recent Transactions</span>
        <a href="<?= e(url('index.php?action=admin/transactions')) ?>" class="text-xs text-indigo-600 hover:underline font-normal">View all →</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>When</th><th>User</th><th>Type</th><th class="text-right">Amount</th><th>Reference</th><th class="text-right">Balance</th></tr></thead>
            <tbody>
            <?php if (empty($recentTx)): ?>
                <tr><td colspan="6">
                    <div class="empty-state">
                        <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        No transactions yet.
                    </div>
                </td></tr>
            <?php else: foreach ($recentTx as $t): ?>
                <tr>
                    <td class="text-slate-500 text-xs whitespace-nowrap"><?= e(dt_fmt($t['created_at'])) ?></td>
                    <td>
                        <div class="font-medium text-slate-800"><?= e($t['user_name'] ?? '-') ?></div>
                        <div class="text-xs text-slate-400"><?= e($t['user_type']) ?></div>
                    </td>
                    <td><span class="badge badge-<?= e($t['type']) ?>"><?= e(strtoupper($t['type'])) ?></span></td>
                    <td class="text-right font-semibold num"><?= e(money($t['amount'])) ?></td>
                    <td class="text-xs text-slate-500"><?= e($t['reference']) ?></td>
                    <td class="text-right text-slate-600 num"><?= e(money((float)($t['balance_after'] ?? 0))) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?= $extraJs ?? '' ?>
