<div class="page-header">
    <div>
        <h1 class="page-title">Parents</h1>
        <p class="page-subtitle">Manage parent accounts and child links</p>
    </div>
    <button onclick="document.getElementById('addParent').classList.toggle('hidden')" class="btn btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Parent
    </button>
</div>

<form method="get" class="card mb-4">
    <div class="card-body flex flex-wrap gap-3 items-end">
        <input type="hidden" name="action" value="admin/parents">
        <div class="flex-1 min-w-[200px]">
            <label class="form-label">Search</label>
            <input type="text" name="q" class="form-input" value="<?= e($q) ?>" placeholder="Name, parent ID, or email">
        </div>
        <button class="btn btn-secondary">Search</button>
        <?php if ($q !== ''): ?><a href="<?= e(url('index.php?action=admin/parents')) ?>" class="btn btn-ghost">Reset</a><?php endif; ?>
    </div>
</form>

<div id="addParent" class="card mb-4 <?= empty($edit) ? 'hidden' : '' ?>">
    <div class="card-header"><?= $edit ? 'Edit Parent' : 'Add New Parent' ?></div>
    <div class="card-body">
        <form method="post" action="<?= e(url('index.php?action=admin/parent-save')) ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">
            <div><label class="form-label">Full Name *</label><input class="form-input" name="full_name" required value="<?= e($edit['full_name'] ?? '') ?>"></div>
            <div><label class="form-label">Email *</label><input class="form-input" type="email" name="email" required value="<?= e($edit['email'] ?? '') ?>"></div>
            <div><label class="form-label">Phone</label><input class="form-input" name="phone" value="<?= e($edit['phone'] ?? '') ?>"></div>
            <div class="md:col-span-2"><label class="form-label">Address</label><textarea class="form-textarea" name="address" rows="2"><?= e($edit['address'] ?? '') ?></textarea></div>
            <div class="md:col-span-2 flex gap-2">
                <button class="btn btn-primary"><?= $edit ? 'Update Parent' : 'Create Parent' ?></button>
                <?php if ($edit): ?><a href="<?= e(url('index.php?action=admin/parents')) ?>" class="btn btn-secondary">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Parent ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Child</th><th class="text-right">Wallet</th></tr></thead>
            <tbody>
            <?php if (empty($parents)): ?>
                <tr><td colspan="6">
                    <div class="empty-state">No parents found.</div>
                </td></tr>
            <?php else: foreach ($parents as $p): ?>
                <tr>
                    <td><span class="num text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded"><?= e($p['parent_id']) ?></span></td>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-indigo-400 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                <?= e(strtoupper(substr($p['full_name'], 0, 1))) ?>
                            </div>
                            <a class="font-medium text-slate-800 hover:text-indigo-600" href="?action=admin/parents&edit=<?= e($p['id']) ?>"><?= e($p['full_name']) ?></a>
                        </div>
                    </td>
                    <td class="text-slate-600"><?= e($p['email']) ?></td>
                    <td class="text-slate-600"><?= e($p['phone']) ?></td>
                    <td><?= e($p['child_name'] ?? '—') ?></td>
                    <td class="text-right font-semibold num text-emerald-700"><?= e(money((float)$p['wallet_balance'])) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
