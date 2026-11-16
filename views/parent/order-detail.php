<div class="page-header">
    <div>
        <h1 class="page-title">Order Detail</h1>
        <p class="page-subtitle"><?= e($order['order_number']) ?></p>
    </div>
    <a class="btn btn-secondary" href="<?= e(url('index.php?action=parent/child-orders')) ?>">← Back</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-wider text-slate-500">Order</div>
                <div class="text-xl font-bold mt-0.5"><code class="bg-slate-100 px-2 py-0.5 rounded"><?= e($order['order_number']) ?></code></div>
                <div class="text-sm text-slate-500 mt-1"><?= e($order['student_name']) ?> (<?= e($order['student_code']) ?>) &middot; <?= e(dt_fmt($order['created_at'])) ?></div>
            </div>
            <div class="text-right">
                <div class="text-3xl font-extrabold text-indigo-700 num"><?= e(money((float)$order['total_amount'])) ?></div>
                <span class="badge badge-<?= e($order['status']) ?> mt-1"><?= e(ucfirst(str_replace('_',' ',$order['status']))) ?></span>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($order['pickup_token']) && in_array($order['status'], ['ready','picked_up'], true)): ?>
<div class="card mb-4">
    <div class="card-header">Pickup Token</div>
    <div class="card-body">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <?php if ($order['status'] === 'ready'): ?>
                    <div class="token-display">
                        <?php for ($i = 0; $i < strlen($order['pickup_token']); $i++): ?>
                            <span class="token-digit"><?= e($order['pickup_token'][$i]) ?></span>
                        <?php endfor; ?>
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
                    <p class="text-sm text-slate-500 mt-2">Your child should show this 6-digit token to the canteen staff.</p>
                <?php elseif ($order['status'] === 'picked_up'): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="font-semibold text-emerald-700">Order Collected</div>
                            <?php if (!empty($order['picked_up_at'])): ?>
                                <div class="text-sm text-slate-500">Completed at <?= e(dt_fmt($order['picked_up_at'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Items</div>
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
