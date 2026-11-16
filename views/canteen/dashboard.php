<div class="page-header">
    <div>
        <h1 class="page-title">Canteen Dashboard</h1>
        <p class="page-subtitle">Today's performance at a glance</p>
    </div>
    <a href="<?= e(url('index.php?action=canteen/orders')) ?>" class="btn btn-primary">Manage Orders</a>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="kpi-card kpi-emerald">
        <div class="kpi-label">Today's Earnings</div>
        <div class="kpi-value"><?= e(money($todayEarnings)) ?></div>
        <div class="kpi-sub"><?= e(date('D, d M')) ?></div>
    </div>
    <div class="kpi-card kpi-blue">
        <div class="kpi-label">Orders Today</div>
        <div class="kpi-value"><?= e(number_format($todayOrders)) ?></div>
        <div class="kpi-sub">Completed: <?= e($todayDone) ?></div>
    </div>
    <div class="kpi-card kpi-amber">
        <div class="kpi-label">Pending</div>
        <div class="kpi-value"><?= e(number_format($pending)) ?></div>
        <div class="kpi-sub">Awaiting action</div>
    </div>
    <div class="kpi-card kpi-pink">
        <div class="kpi-label">Quick Action</div>
        <div class="mt-2">
            <a class="btn btn-secondary w-full !text-pink-700 !bg-white" href="<?= e(url('index.php?action=canteen/orders&status=pending')) ?>">View Pending →</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header">Monthly Income</div>
        <div class="card-body"><canvas id="monthlyChart" height="200"></canvas></div>
    </div>
    <div class="card">
        <div class="card-header">Top Selling (7 days)</div>
        <div class="card-body">
            <?php if (empty($top)): ?>
                <p class="text-slate-500 text-sm">No data yet.</p>
            <?php else: foreach ($top as $t): ?>
                <div class="flex items-center justify-between py-2.5 border-b border-slate-100 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-pink-400 flex items-center justify-center text-white text-xs font-bold">
                            #<?= e(array_search($t, $top) + 1) ?>
                        </div>
                        <span class="font-medium text-slate-700 text-sm"><?= e($t['name']) ?></span>
                    </div>
                    <span class="badge bg-indigo-100 text-indigo-700"><?= e($t['qty']) ?> pcs</span>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?= $extraJs ?? '' ?>
