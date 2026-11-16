<div class="page-header">
    <div>
        <h1 class="page-title">All Orders</h1>
        <p class="page-subtitle">View, filter, and export every order</p>
    </div>
    <div class="flex gap-2">
        <a class="btn btn-secondary btn-sm" href="<?= e(url('index.php?action=admin/export&kind=orders&format=csv')) ?>">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            CSV
        </a>
        <a class="btn btn-primary btn-sm" href="<?= e(url('index.php?action=admin/export&kind=orders&format=pdf')) ?>">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            PDF
        </a>
    </div>
</div>

<form method="get" class="card mb-4">
    <input type="hidden" name="action" value="admin/orders">
    <div class="card-body grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div><label class="form-label">From</label><input type="date" class="form-input" name="from" value="<?= e($from) ?>"></div>
        <div><label class="form-label">To</label><input type="date" class="form-input" name="to" value="<?= e($to) ?>"></div>
        <div>
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="">All</option>
                <?php foreach (['pending','accepted','preparing','ready','picked_up','cancelled'] as $st): ?>
                    <option value="<?= $st ?>" <?= $status===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label class="form-label">Student</label><input class="form-input" name="student" value="<?= e($student) ?>" placeholder="Name or ID"></div>
        <button class="btn btn-secondary"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>Filter</button>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Order #</th><th>Student</th><th class="text-right">Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="5"><div class="empty-state">No orders found.</div></td></tr>
            <?php else: foreach ($orders as $o): ?>
                <tr>
                    <td><span class="num text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded"><?= e($o['order_number']) ?></span></td>
                    <td>
                        <div class="font-medium text-slate-800"><?= e($o['student_name']) ?></div>
                        <div class="text-xs text-slate-400"><?= e($o['student_code']) ?></div>
                    </td>
                    <td class="text-right font-semibold num"><?= e(money((float)$o['total_amount'])) ?></td>
                    <td><span class="badge badge-<?= e($o['status']) ?>"><?= e(ucfirst(str_replace('_',' ',$o['status']))) ?></span></td>
                    <td class="text-slate-500 text-xs whitespace-nowrap"><?= e(dt_fmt($o['created_at'])) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
