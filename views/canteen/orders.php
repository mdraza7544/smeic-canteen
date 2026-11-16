<div class="page-header">
    <div>
        <h1 class="page-title">Order Management</h1>
        <p class="page-subtitle">Accept, prepare, and hand out orders</p>
    </div>
    <a class="btn btn-secondary" href="<?= e(url('index.php?action=canteen/token-verification')) ?>">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
        Token Verification
    </a>
</div>

<form method="get" class="card mb-4">
    <input type="hidden" name="action" value="canteen/orders">
    <div class="card-body flex flex-wrap items-end gap-3">
        <div>
            <label class="form-label">Status</label>
            <select class="form-select" name="status" onchange="this.form.submit()">
                <option value="">All</option>
                <?php foreach (['pending','accepted','preparing','ready','picked_up','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</form>

<?php if (empty($orders)): ?>
    <div class="card"><div class="card-body"><div class="empty-state">
        <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        No orders to display.
    </div></div></div>
<?php else: ?>
<div class="space-y-3">
<?php foreach ($orders as $o):
    $ois = $items[$o['id']] ?? []; ?>
    <div class="card card-hover">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="num text-sm font-bold bg-slate-100 text-slate-800 px-2.5 py-1 rounded"><?= e($o['order_number']) ?></span>
                        <span class="badge badge-<?= e($o['status']) ?>"><?= e(ucfirst(str_replace('_',' ',$o['status']))) ?></span>
                        <?php if (!empty($o['prepared_time_minutes'])): ?>
                            <span class="badge bg-slate-100 text-slate-700">⏱ <?= e($o['prepared_time_minutes']) ?> min</span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2 flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-400 to-fuchsia-400 flex items-center justify-center text-white text-xs font-bold">
                            <?= e(strtoupper(substr($o['student_name'], 0, 1))) ?>
                        </div>
                        <div>
                            <div class="font-medium text-slate-800 text-sm"><?= e($o['student_name']) ?></div>
                            <div class="text-xs text-slate-400"><?= e($o['student_code']) ?> &middot; <?= e(dt_fmt($o['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-extrabold text-indigo-700 num"><?= e(money((float)$o['total_amount'])) ?></div>
                </div>
            </div>

            <div class="mt-3 text-sm text-slate-600 space-y-1">
                <?php foreach ($ois as $it): ?>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400">•</span>
                        <span class="font-medium text-slate-700"><?= e($it['name']) ?></span>
                        <span class="text-slate-400">×<?= e($it['quantity']) ?></span>
                        <span class="text-slate-400 ml-auto num text-xs"><?= e(money((float)$it['price'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <?php if ($o['status'] === 'pending'): ?>
                    <form method="post" action="<?= e(url('index.php?action=canteen/accept-order')) ?>" class="flex items-center gap-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($o['id']) ?>">
                        <div class="inline-flex items-center border border-slate-200 rounded-lg overflow-hidden">
                            <span class="px-2 text-xs text-slate-500 bg-slate-50">⏱</span>
                            <input type="number" name="prep_time" value="10" min="1" class="w-16 !py-1.5 !px-1 text-center text-sm border-0 focus:ring-0 focus:outline-none" title="Prep time (min)">
                            <span class="px-2 text-xs text-slate-500 bg-slate-50">min</span>
                        </div>
                        <button class="btn btn-primary btn-sm">✓ Accept</button>
                    </form>
                <?php endif; ?>
                <?php if (in_array($o['status'], ['accepted','preparing'], true)): ?>
                    <form method="post" action="<?= e(url('index.php?action=canteen/mark-ready')) ?>">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($o['id']) ?>">
                        <button class="btn btn-success btn-sm">✓ Mark Ready</button>
                    </form>
                <?php endif; ?>
                <?php if ($o['status'] === 'ready'): ?>
                    <form method="post" action="<?= e(url('index.php?action=canteen/mark-pickedup')) ?>">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($o['id']) ?>">
                        <button class="btn btn-secondary btn-sm">📦 Picked Up</button>
                    </form>
                    <span class="text-xs text-slate-500 self-center">Token: <code class="bg-slate-100 px-1.5 py-0.5 rounded tracking-widest font-bold text-indigo-700"><?= e($o['pickup_token']) ?></code></span>
                <?php endif; ?>
                <?php if (in_array($o['status'], ['pending','accepted','preparing','ready'], true)): ?>
                    <form method="post" action="<?= e(url('index.php?action=canteen/cancel-order')) ?>" onsubmit="return confirmAction('Cancel this order? A refund will be issued to the student.')">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($o['id']) ?>">
                        <button class="btn btn-danger btn-sm">Cancel</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($status)): ?>
<script>
// Live polling for new orders
setTimeout(() => location.reload(), 20000);
</script>
<?php endif; ?>
