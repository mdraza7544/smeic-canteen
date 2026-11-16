<div class="page-header">
    <div>
        <h1 class="page-title">Order Tracking</h1>
        <p class="page-subtitle">Live status of your order</p>
    </div>
    <a class="btn btn-secondary" href="<?= e(url('index.php?action=student/orders')) ?>">← All orders</a>
</div>

<?php if (!$order): ?>
    <div class="card"><div class="card-body"><div class="empty-state">
        <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        No active or recent orders.
        <div class="mt-3"><a class="btn btn-primary" href="<?= e(url('index.php?action=student/menu')) ?>">Place an order</a></div>
    </div></div></div>
<?php else: ?>

<div class="card mb-4">
    <div class="card-body">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-wider text-slate-500">Order</div>
                <div class="text-xl font-bold mt-0.5"><code class="bg-slate-100 px-2 py-0.5 rounded"><?= e($order['order_number']) ?></code></div>
                <div class="text-sm text-slate-500 mt-1">Placed <?= e(dt_fmt($order['created_at'])) ?></div>
            </div>
            <div class="text-right">
                <div class="text-3xl font-extrabold text-indigo-700 num"><?= e(money((float)$order['total_amount'])) ?></div>
                <span class="badge badge-<?= e($order['status']) ?> mt-1"><?= e(ucfirst(str_replace('_',' ',$order['status']))) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Status Timeline</div>
    <div class="card-body">
        <?php
        $steps = ['pending'=>'Order Placed','accepted'=>'Accepted','preparing'=>'Preparing','ready'=>'Ready for Pickup','picked_up'=>'Picked Up'];
        $currentIdx = array_search($order['status'], array_keys($steps), true);
        if ($currentIdx === false) $currentIdx = 0;
        ?>
        <div class="timeline">
            <?php $i = 0; foreach ($steps as $key => $label):
                $cls = '';
                if ($i < $currentIdx) $cls = 'done';
                elseif ($i === $currentIdx) $cls = 'done current';
                $badge = match(true) {
                    $i < $currentIdx => '✓',
                    $i === $currentIdx => '●',
                    default => ''
                };
            ?>
                <div class="timeline-step <?= $cls ?>">
                    <div class="dot"></div>
                    <div>
                        <div class="font-semibold text-sm"><?= $badge ?> <?= e($label) ?></div>
                        <?php if ($key === 'accepted' && !empty($order['prepared_time_minutes'])): ?>
                            <div class="text-xs text-slate-500">Prep: <?= e($order['prepared_time_minutes']) ?> min</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($i < count($steps) - 1): ?><div class="timeline-connector <?= $i < $currentIdx ? 'done' : '' ?>"></div><?php endif; ?>
            <?php $i++; endforeach; ?>
        </div>
    </div>
</div>

<?php if ($order['status'] === 'ready'): ?>
<div class="card mb-4 border-emerald-200 bg-gradient-to-r from-emerald-50 to-green-50">
    <div class="card-body text-center py-8">
        <div class="text-5xl mb-3">🎉</div>
        <div class="text-2xl font-bold text-emerald-800">Your order is ready!</div>
        <p class="text-sm text-emerald-700 mt-1">Show this token to the canteen staff to collect your order</p>
        <?php if (!empty($order['pickup_token'])): ?>
            <div class="mt-4">
                <div class="token-display">
                    <?php for ($i = 0; $i < strlen($order['pickup_token']); $i++): ?>
                        <span class="token-digit"><?= e($order['pickup_token'][$i]) ?></span>
                    <?php endfor; ?>
                </div>
            </div>
            <?php if (!empty($order['token_generated_at'])): ?>
                <?php
                    $generated = strtotime($order['token_generated_at']);
                    $expiresAt = $generated + (TOKEN_EXPIRY_HOURS * 3600);
                    $remaining = $expiresAt - time();
                    if ($remaining > 0) {
                        $hours = (int)floor($remaining / 3600);
                        $minutes = (int)floor(($remaining % 3600) / 60);
                        echo '<div class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 text-sm font-medium"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Expires in ' . $hours . 'h ' . $minutes . 'm</div>';
                    }
                ?>
            <?php endif; ?>
        <?php endif; ?>
        <form method="post" action="<?= e(url('index.php?action=student/pickup')) ?>" class="mt-5">
            <?= csrf_field() ?>
            <input type="hidden" name="order_id" value="<?= e($order['id']) ?>">
            <button class="btn btn-success !py-3 !px-6 text-base">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                I have picked up the order
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Items in this order</div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Item</th><th>Qty</th><th class="text-right">Price</th><th class="text-right">Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= e($it['name']) ?></td>
                    <td><span class="badge bg-slate-100 text-slate-700">×<?= e($it['quantity']) ?></span></td>
                    <td class="text-right num"><?= e(money((float)$it['price'])) ?></td>
                    <td class="text-right font-semibold num"><?= e(money((float)$it['price'] * (int)$it['quantity'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">Order Reference</div>
    <div class="card-body">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-slate-500 text-xs uppercase tracking-wider">Order Number</div>
                <div class="font-mono font-bold text-slate-800 mt-1"><?= e($order['order_number']) ?></div>
            </div>
            <div>
                <div class="text-slate-500 text-xs uppercase tracking-wider">Pickup Token</div>
                <div class="font-mono font-bold text-indigo-700 mt-1 text-lg tracking-widest"><?= e($order['pickup_token']) ?></div>
            </div>
        </div>
        <p class="text-xs text-slate-500 mt-3">Show this token to the canteen staff when collecting your order, or open your token page from your account.</p>
    </div>
</div>

<?php if ($order && in_array($order['status'], ['pending','accepted','preparing','ready'], true)): ?>
<script>
setInterval(async () => {
    try {
        const r = await smeicAjax('api/get-order-status.php?id=<?= e((int)($order['id'] ?? 0)) ?>');
        if (r.data && r.data.data && r.data.data.status && r.data.data.status !== '<?= e($order['status']) ?>') {
            location.reload();
        }
    } catch (e) {}
}, 10000);
</script>
<?php endif; ?>
<?php endif; ?>
