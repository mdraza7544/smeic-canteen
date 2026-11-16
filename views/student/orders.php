<div class="page-header">
    <div>
        <h1 class="page-title">My Orders</h1>
        <p class="page-subtitle">All your past and current orders</p>
    </div>
</div>
<?php if (empty($orders)): ?>
    <div class="card"><div class="card-body"><div class="empty-state">
        <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        No orders yet.
        <div class="mt-3"><a class="btn btn-primary" href="<?= e(url('index.php?action=student/menu')) ?>">Place your first order</a></div>
    </div></div></div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Order #</th><th class="text-right">Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><span class="num text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded"><?= e($o['order_number']) ?></span></td>
                    <td class="text-right font-semibold num"><?= e(money((float)$o['total_amount'])) ?></td>
                    <td><span class="badge badge-<?= e($o['status']) ?>"><?= e(ucfirst(str_replace('_',' ',$o['status']))) ?></span></td>
                    <td class="text-slate-500 text-xs"><?= e(dt_fmt($o['created_at'])) ?></td>
                    <td class="text-right"><a class="btn btn-secondary btn-sm" href="<?= e(url('index.php?action=student/order-tracking&id=' . $o['id'])) ?>">Track</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php endif; ?>
