<div class="page-header">
    <div>
        <h1 class="page-title">Menu Management</h1>
        <p class="page-subtitle">Add, edit, and manage canteen offerings</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('form').classList.remove('hidden')">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Item
    </button>
</div>

<div id="form" class="card mb-4 <?= empty($edit) ? 'hidden' : '' ?>">
    <div class="card-header"><?= !empty($edit) ? 'Edit Menu Item' : 'Add New Menu Item' ?></div>
    <div class="card-body">
        <form method="post" action="<?= e(url('index.php?action=canteen/menu-save')) ?>" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">
            <input type="hidden" name="existing_image" value="<?= e($edit['image'] ?? '') ?>">
            <div><label class="form-label">Name *</label><input class="form-input" name="name" required value="<?= e($edit['name'] ?? '') ?>"></div>
            <div>
                <label class="form-label">Category</label>
                <select class="form-select" name="category">
                    <?php foreach (['veg','nonveg','snacks','drinks','desserts'] as $c): ?>
                        <option value="<?= $c ?>" <?= ($edit['category'] ?? '')===$c?'selected':'' ?>><?= ucfirst($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2"><label class="form-label">Description</label><textarea class="form-textarea" name="description" rows="2"><?= e($edit['description'] ?? '') ?></textarea></div>
            <div><label class="form-label">Price (<?= CURRENCY ?>) *</label><input type="number" min="1" step="0.01" class="form-input" name="price" required value="<?= e($edit['price'] ?? '') ?>"></div>
            <div><label class="form-label">Image (jpg/png/webp, max 4MB)</label><input type="file" accept="image/*" class="form-input" name="image"></div>
            <div class="md:col-span-2 flex items-center gap-2">
                <input id="avail" type="checkbox" name="is_available" value="1" <?= !isset($edit)||$edit['is_available']?'checked':'' ?> class="w-4 h-4 text-indigo-600 rounded">
                <label for="avail" class="text-sm text-slate-700">Available for ordering</label>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button class="btn btn-primary"><?= !empty($edit) ? 'Update' : 'Create' ?> Item</button>
                <a href="<?= e(url('index.php?action=canteen/menu')) ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
        <?php if (!empty($edit['image'])): ?>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <div class="text-xs text-slate-500 mb-2">Current image:</div>
                <img src="<?= e(menu_image($edit['image'])) ?>" class="h-24 rounded-xl object-cover" alt="">
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="menu-grid">
    <?php if (empty($items)): ?>
        <div class="col-span-full card"><div class="card-body"><div class="empty-state">No menu items yet. Add your first one!</div></div></div>
    <?php else: foreach ($items as $i): ?>
        <div class="menu-card">
            <img src="<?= e(menu_image($i['image'])) ?>" class="menu-img" alt="">
            <div class="menu-body">
                <div class="flex items-center justify-between gap-2">
                    <span class="badge badge-<?= e($i['category']) ?>"><?= e(ucfirst($i['category'])) ?></span>
                    <span class="text-lg font-extrabold text-indigo-700 num"><?= e(money((float)$i['price'])) ?></span>
                </div>
                <div class="menu-name"><?= e($i['name']) ?></div>
                <div class="menu-desc"><?= e($i['description']) ?></div>
                <div class="flex items-center justify-between gap-1 mt-1">
                    <form method="post" action="<?= e(url('index.php?action=canteen/menu-save')) ?>" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($i['id']) ?>">
                        <input type="hidden" name="name" value="<?= e($i['name']) ?>">
                        <input type="hidden" name="description" value="<?= e($i['description']) ?>">
                        <input type="hidden" name="price" value="<?= e($i['price']) ?>">
                        <input type="hidden" name="category" value="<?= e($i['category']) ?>">
                        <input type="hidden" name="existing_image" value="<?= e($i['image']) ?>">
                        <input type="hidden" name="is_available" value="<?= $i['is_available']?0:1 ?>">
                        <button class="badge <?= $i['is_available']?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-700' ?> cursor-pointer">
                            <?= $i['is_available']?'● Available':'● Hidden' ?>
                        </button>
                    </form>
                    <div class="flex gap-1">
                        <a class="btn btn-secondary btn-sm" href="?action=canteen/menu&edit=<?= e($i['id']) ?>">Edit</a>
                        <form method="post" action="<?= e(url('index.php?action=canteen/menu-delete')) ?>" class="inline" onsubmit="return confirmAction('Delete this item?')">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($i['id']) ?>">
                            <button class="btn btn-danger btn-sm">×</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>
