<div class="page-header">
    <div>
        <h1 class="page-title">Students</h1>
        <p class="page-subtitle">Manage all student accounts</p>
    </div>
    <button onclick="document.getElementById('addStudent').classList.toggle('hidden')" class="btn btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Student
    </button>
</div>

<form method="get" class="card mb-4">
    <div class="card-body flex flex-wrap gap-3 items-end">
        <input type="hidden" name="action" value="admin/students">
        <div class="flex-1 min-w-[200px]">
            <label class="form-label">Search</label>
            <input type="text" name="q" class="form-input" value="<?= e($q) ?>" placeholder="Name, class, roll, or student ID">
        </div>
        <button class="btn btn-secondary">Search</button>
        <?php if ($q !== ''): ?><a href="<?= e(url('index.php?action=admin/students')) ?>" class="btn btn-ghost">Reset</a><?php endif; ?>
    </div>
</form>

<div id="addStudent" class="card mb-4 <?= empty($edit) ? 'hidden' : '' ?>">
    <div class="card-header"><?= $edit ? 'Edit Student' : 'Add New Student' ?></div>
    <div class="card-body">
        <form method="post" action="<?= e(url('index.php?action=admin/student-save')) ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">
            <div><label class="form-label">Full Name *</label><input class="form-input" name="full_name" required value="<?= e($edit['full_name'] ?? '') ?>"></div>
            <div><label class="form-label">Class</label><input class="form-input" name="class_name" value="<?= e($edit['class_name'] ?? '') ?>" placeholder="e.g. 10-A"></div>
            <div><label class="form-label">Roll No</label><input class="form-input" name="roll_no" value="<?= e($edit['roll_no'] ?? '') ?>"></div>
            <div><label class="form-label">Behavioral Score</label><input class="form-input" type="number" name="behavioral_score" value="<?= e($edit['behavioral_score'] ?? 100) ?>"></div>
            <div class="md:col-span-2"><label class="form-label">Address</label><textarea class="form-textarea" name="address" rows="2"><?= e($edit['address'] ?? '') ?></textarea></div>
            <div class="md:col-span-2">
                <label class="form-label">Parent / Guardian</label>
                <select class="form-select" name="parent_id">
                    <option value="">— None —</option>
                    <?php foreach ($parents as $p): ?>
                        <option value="<?= e($p['id']) ?>" <?= (($edit['parent_id'] ?? 0) == $p['id']) ? 'selected' : '' ?>>
                            <?= e($p['parent_id'] . ' — ' . $p['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button class="btn btn-primary"><?= $edit ? 'Update Student' : 'Create Student' ?></button>
                <?php if ($edit): ?><a href="<?= e(url('index.php?action=admin/students')) ?>" class="btn btn-secondary">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>ID</th><th>Name</th><th>Class</th><th>Roll</th><th>Parent</th><th class="text-right">Balance</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            <?php if (empty($students)): ?>
                <tr><td colspan="7">
                    <div class="empty-state">
                        <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        No students found.
                    </div>
                </td></tr>
            <?php else: foreach ($students as $s): ?>
                <tr>
                    <td><span class="num text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded"><?= e($s['student_id']) ?></span></td>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-fuchsia-400 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                <?= e(strtoupper(substr($s['full_name'], 0, 1))) ?>
                            </div>
                            <div class="font-medium text-slate-800"><?= e($s['full_name']) ?></div>
                        </div>
                    </td>
                    <td><?= e($s['class_name']) ?></td>
                    <td><?= e($s['roll_no']) ?></td>
                    <td>
                        <?php if (!empty($s['parent_name'])): ?>
                            <div class="text-sm"><?= e($s['parent_name']) ?></div>
                            <div class="text-xs text-slate-400"><?= e($s['parent_code']) ?></div>
                        <?php else: ?>
                            <span class="text-slate-400 text-xs">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right font-semibold num text-emerald-700"><?= e(money((float)($s['balance'] ?? 0))) ?></td>
                    <td class="actions">
                        <a class="btn btn-secondary btn-sm" href="?action=admin/students&edit=<?= e($s['id']) ?>" title="Edit">Edit</a>
                        <form method="post" action="<?= e(url('index.php?action=admin/student-reset')) ?>" class="inline" onsubmit="return confirmAction('Reset password for this student?')">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($s['id']) ?>">
                            <button class="btn btn-warning btn-sm">Reset</button>
                        </form>
                        <form method="post" action="<?= e(url('index.php?action=admin/student-delete')) ?>" class="inline" onsubmit="return confirmAction('Delete this student?')">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($s['id']) ?>">
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
