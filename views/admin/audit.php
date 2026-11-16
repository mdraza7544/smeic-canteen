<div class="page-header">
    <div>
        <h1 class="page-title">Audit Log</h1>
        <p class="page-subtitle">Recent administrative actions</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>When</th><th>Admin</th><th>Action</th><th>Details</th></tr></thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="4"><div class="empty-state">No audit entries yet.</div></td></tr>
            <?php else: foreach ($logs as $a): ?>
                <tr>
                    <td class="text-slate-500 text-xs whitespace-nowrap"><?= e(dt_fmt($a['created_at'])) ?></td>
                    <td class="text-slate-700"><?= e($a['admin_email'] ?? '-') ?></td>
                    <td><span class="badge bg-slate-100 text-slate-700"><?= e($a['action']) ?></span></td>
                    <td class="text-sm text-slate-600"><?= e($a['details']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
