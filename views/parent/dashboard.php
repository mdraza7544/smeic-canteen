<div class="page-header">
    <div>
        <h1 class="page-title">Welcome, <?= e($parent['full_name']) ?></h1>
        <p class="page-subtitle">Parent ID: <code class="bg-slate-100 px-1.5 py-0.5 rounded"><?= e($parent['parent_id']) ?></code></p>
    </div>
    <a class="btn btn-primary" href="<?= e(url('index.php?action=parent/transfer')) ?>">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        Transfer Funds
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-6">
    <div class="kpi-card kpi-indigo">
        <div class="kpi-label">Your Wallet</div>
        <div class="kpi-value"><?= e(money((float)$parent['wallet_balance'])) ?></div>
        <div class="kpi-sub">Available</div>
    </div>
    <?php foreach ($children as $c): ?>
        <div class="kpi-card kpi-emerald">
            <div class="kpi-label"><?= e($c['full_name']) ?>'s Wallet</div>
            <div class="kpi-value"><?= e(money((float)($c['balance'] ?? 0))) ?></div>
            <div class="kpi-sub">Behavior: <strong><?= e($c['behavioral_score']) ?></strong>/100</div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (!empty($special)): ?>
<div class="card mb-4 border-amber-200 bg-gradient-to-r from-amber-50 to-pink-50">
    <div class="card-body flex items-center gap-4">
        <img src="<?= e(menu_image($special['image'])) ?>" class="w-16 h-16 rounded-xl object-cover ring-2 ring-amber-200" alt="">
        <div class="flex-1 min-w-0">
            <div class="text-xs uppercase tracking-wider text-amber-700 font-bold">★ Today's Special</div>
            <div class="font-semibold text-slate-800"><?= e($special['name']) ?> &middot; <?= e(money((float)$special['price'])) ?></div>
        </div>
        <a class="btn btn-warning" href="<?= e(url('index.php?action=parent/preferences')) ?>">Suggest to child</a>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header">
            <span>Child's Recent Orders</span>
            <a class="text-xs text-indigo-600 hover:underline font-normal" href="<?= e(url('index.php?action=parent/child-orders')) ?>">View all →</a>
        </div>
        <div class="card-body">
            <?php if (empty($children)): ?>
                <p class="text-slate-500 text-sm">No children linked. Contact admin.</p>
            <?php else: foreach ($children as $c):
                $orders = $childOrders[$c['id']] ?? []; ?>
                <div class="mb-4 last:mb-0">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-400 to-indigo-400 flex items-center justify-center text-white text-xs font-bold">
                            <?= e(strtoupper(substr($c['full_name'], 0, 1))) ?>
                        </div>
                        <div class="font-semibold text-slate-700 text-sm"><?= e($c['full_name']) ?> <span class="text-slate-400 text-xs font-normal"><?= e($c['student_id']) ?></span></div>
                    </div>
                    <?php if (empty($orders)): ?>
                        <p class="text-slate-400 text-xs pl-9">No orders yet.</p>
                    <?php else: foreach (array_slice($orders, 0, 3) as $o): ?>
                        <div class="flex items-center justify-between text-sm py-1.5 pl-9 border-b border-slate-50 last:border-0">
                            <a class="text-indigo-600 hover:underline text-xs num" href="<?= e(url('index.php?action=parent/order-detail&id=' . $o['id'])) ?>"><?= e($o['order_number']) ?></a>
                            <span class="text-xs font-medium num"><?= e(money((float)$o['total_amount'])) ?></span>
                            <span class="badge badge-<?= e($o['status']) ?>"><?= e(ucfirst(str_replace('_',' ',$o['status']))) ?></span>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Spending by Category</div>
        <div class="card-body">
            <?php if (empty($spendByCategory)): ?>
                <p class="text-slate-500 text-sm">No spending recorded yet.</p>
            <?php else: ?>
                <canvas id="spendChart" height="220"></canvas>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($spendByCategory)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('spendChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($spendByCategory)) ?>,
            datasets: [{ data: <?= json_encode(array_values($spendByCategory)) ?>, backgroundColor: ['#4f46e5','#10b981','#f59e0b','#ef4444','#06b6d4','#ec4899'] }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
<?php endif; ?>
