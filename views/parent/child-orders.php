<div class="page-header">
    <div>
        <h1 class="page-title">Child Orders</h1>
        <p class="page-subtitle">Track your child's order history</p>
    </div>
</div>

<form method="get" class="card mb-4">
    <input type="hidden" name="action" value="parent/child-orders">
    <div class="card-body grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div>
            <label class="form-label">Child</label>
            <select class="form-select" name="child_id">
                <option value="">— Select —</option>
                <?php foreach ($children as $c): ?>
                    <option value="<?= e($c['id']) ?>" <?= $childId==(int)$c['id']?'selected':'' ?>><?= e($c['student_id'] . ' — ' . $c['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label class="form-label">From</label><input type="date" class="form-input" name="from" value="<?= e($from) ?>"></div>
        <div><label class="form-label">To</label><input type="date" class="form-input" name="to" value="<?= e($to) ?>"></div>
        <button class="btn btn-secondary">Filter</button>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Order #</th><th class="text-right">Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="5"><div class="empty-state">No orders to display.</div></td></tr>
            <?php else: foreach ($orders as $o): ?>
                <tr>
                    <td><span class="num text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded"><?= e($o['order_number']) ?></span></td>
                    <td class="text-right font-semibold num"><?= e(money((float)$o['total_amount'])) ?></td>
                    <td><span class="badge badge-<?= e($o['status']) ?>"><?= e(ucfirst(str_replace('_',' ',$o['status']))) ?></span></td>
                    <td class="text-slate-500 text-xs"><?= e(dt_fmt($o['created_at'])) ?></td>
                    <td class="text-right"><a class="btn btn-secondary btn-sm" href="<?= e(url('index.php?action=parent/order-detail&id=' . $o['id'])) ?>">View</a></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
