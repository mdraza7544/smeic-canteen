<?php
$editing = $editing ?? null;
$manager = $manager ?? null;
$staff = $staff ?? [];
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Staff Management</h1>
        <p class="page-subtitle">Manage canteen staff accounts and permissions</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="badge badge-ready">Max 3 staff members</span>
        <a class="btn btn-secondary" href="<?= e(url('index.php?action=canteen/profile')) ?>">Back to profile</a>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div><?= e($msg) ?></div>
    </div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div><?= e($err) ?></div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="card lg:col-span-1">
        <div class="card-header"><?= $editing ? 'Edit Staff Member' : 'Add Staff Member' ?></div>
        <div class="card-body">
            <form method="post" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="staff_action" value="save">
                <input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">

                <div>
                    <label class="form-label">Full Name</label>
                    <input class="form-input" name="full_name" required value="<?= e($editing['full_name'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input class="form-input" name="phone" value="<?= e($editing['phone'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input class="form-input" type="email" name="email" required value="<?= e($editing['email'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label">Username</label>
                    <input class="form-input" name="username" required value="<?= e($editing['username'] ?? ($editing['email'] ?? '')) ?>">
                </div>
                <div>
                    <label class="form-label">Password <?= $editing ? '(leave blank to keep current)' : '' ?></label>
                    <input class="form-input" type="password" name="password" <?= $editing ? '' : 'required' ?> autocomplete="new-password">
                </div>

                <div class="grid grid-cols-1 gap-2 text-sm">
                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="can_verify_tokens" <?= empty($editing) || !empty($editing['can_verify_tokens']) ? 'checked' : '' ?>> Verify Tokens</label>
                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="can_complete_orders" <?= empty($editing) || !empty($editing['can_complete_orders']) ? 'checked' : '' ?>> Complete Orders</label>
                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="can_view_pending_orders" <?= empty($editing) || !empty($editing['can_view_pending_orders']) ? 'checked' : '' ?>> View Pending Orders</label>
                </div>

                <div class="text-xs text-slate-500">
                    Permissions are stored with each staff record and can be reviewed or updated later.
                </div>

                <button class="btn btn-primary w-full !py-3">Save Staff</button>
            </form>
        </div>
    </div>

    <div class="card lg:col-span-2">
        <div class="card-header">
            <span>Current Staff</span>
            <span class="text-xs text-slate-500"><?= count($staff) ?> total</span>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Manager</div>
                    <div class="font-semibold text-slate-800 mt-1"><?= e($manager['full_name'] ?? '-') ?></div>
                    <div class="text-sm text-slate-600"><?= e($manager['email'] ?? '-') ?></div>
                    <div class="mt-2"><span class="badge badge-ready">Manager access</span></div>
                </div>
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Limit</div>
                    <div class="font-semibold text-slate-800 mt-1">Up to 3 staff members</div>
                    <div class="text-sm text-slate-600">Create, disable, or update staff records from this page.</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>State</th>
                            <th>Permissions</th>
                            <th class="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($staff)): ?>
                            <tr><td colspan="6"><div class="empty-state">No staff records found.</div></td></tr>
                        <?php else: foreach ($staff as $row): ?>
                            <tr>
                                <td>
                                    <div class="font-medium text-slate-800"><?= e($row['full_name']) ?></div>
                                    <div class="text-xs text-slate-500"><?= e($row['email']) ?></div>
                                </td>
                                <td class="num"><?= e($row['username'] ?? $row['email']) ?></td>
                                <td><span class="badge badge-accepted"><?= e($row['role']) ?></span></td>
                                <td>
                                    <?php if (!empty($row['is_active'])): ?>
                                        <span class="badge badge-ready">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-cancelled">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-xs text-slate-600">
                                    Verify: <?= !empty($row['can_verify_tokens']) ? 'Yes' : 'No' ?><br>
                                    Complete: <?= !empty($row['can_complete_orders']) ? 'Yes' : 'No' ?><br>
                                    Pending: <?= !empty($row['can_view_pending_orders']) ? 'Yes' : 'No' ?>
                                </td>
                                <td class="actions">
                                    <?php if (($row['role'] ?? '') !== 'manager'): ?>
                                        <a class="btn btn-secondary btn-sm" href="<?= e(url('index.php?action=canteen/settings/staff&edit=' . $row['id'])) ?>">Edit</a>
                                        <form method="post" class="inline-block ml-1" onsubmit="return confirmAction('Delete this staff member?')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="staff_action" value="delete">
                                            <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                        </form>
                                        <form method="post" class="inline-block ml-1">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="staff_action" value="toggle">
                                            <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                            <button class="btn btn-warning btn-sm" type="submit"><?= !empty($row['is_active']) ? 'Disable' : 'Enable' ?></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-500">Protected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
