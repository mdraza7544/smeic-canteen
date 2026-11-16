<div class="page-header">
    <div>
        <h1 class="page-title">Menu</h1>
        <p class="page-subtitle">Browse & order from the canteen</p>
    </div>
    <a class="btn btn-primary relative" href="<?= e(url('index.php?action=student/cart')) ?>">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Cart
        <span data-cart-badge class="absolute -top-1 -right-1 bg-amber-400 text-amber-900 text-xs font-bold rounded-full px-1.5 min-w-[20px] text-center ring-2 ring-white <?= ($cartCount ?? 0) > 0 ? '' : 'hidden' ?>"><?= e((int)($cartCount ?? 0)) ?></span>
    </a>
</div>

<?php if (!empty($special)): ?>
    <div class="special-banner">
        <div class="special-content">
            <img src="<?= e(menu_image($special['image'])) ?>" class="special-img" alt="">
            <div class="flex-1 min-w-0">
                <div class="special-label">★ Today's Special</div>
                <div class="special-name"><?= e($special['name']) ?></div>
                <div class="special-desc truncate"><?= e($special['description']) ?></div>
            </div>
            <div class="text-right flex-shrink-0">
                <div class="text-2xl font-extrabold"><?= e(money((float)$special['price'])) ?></div>
                <button class="btn btn-sm !bg-white !text-amber-700 hover:!bg-amber-50 mt-1" onclick="addToCart(<?= e($special['id']) ?>, 1)">+ Add</button>
            </div>
        </div>
    </div>
<?php endif; ?>

<form method="get" class="card mb-4">
    <input type="hidden" name="action" value="student/menu">
    <div class="card-body flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label">Category</label>
            <select class="form-select" name="cat" onchange="this.form.submit()">
                <option value="">All</option>
                <?php foreach (['veg','nonveg','snacks','drinks','desserts'] as $c): ?>
                    <option value="<?= e($c) ?>" <?= $cat===$c?'selected':'' ?>><?= e(ucfirst($c)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="form-label">Search</label>
            <input class="form-input" name="q" value="<?= e($q) ?>" placeholder="Search items...">
        </div>
        <button class="btn btn-secondary">Apply</button>
    </div>
</form>

<?php if (empty($items)): ?>
    <div class="card"><div class="card-body"><div class="empty-state">No items match your filters.</div></div></div>
<?php else: ?>
<div class="menu-grid">
    <?php foreach ($items as $it): ?>
        <div class="menu-card">
            <img src="<?= e(menu_image($it['image'])) ?>" class="menu-img" alt="">
            <div class="menu-body">
                <div class="flex items-center justify-between gap-2">
                    <span class="badge badge-<?= e($it['category']) ?>"><?= e(ucfirst($it['category'])) ?></span>
                    <span class="text-lg font-extrabold text-indigo-700 num"><?= e(money((float)$it['price'])) ?></span>
                </div>
                <div class="menu-name"><?= e($it['name']) ?></div>
                <div class="menu-desc"><?= e($it['description']) ?></div>
                <div class="flex items-center gap-1 mt-1">
                    <input id="qty_<?= e($it['id']) ?>" type="number" min="1" max="20" value="1" class="form-input !w-14 !py-1 !px-1 text-center text-sm">
                    <button class="btn btn-primary btn-sm flex-1" onclick="addToCart(<?= e($it['id']) ?>, document.getElementById('qty_<?= e($it['id']) ?>').value)">+ Add to cart</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
async function addToCart(itemId, qty) {
    try {
        const r = await smeicCart.add(itemId, qty);
        if (r.data && r.data.ok) {
            toast('Added to cart', 'success');
            if (typeof r.data.cart_count !== 'undefined') smeicCart.updateBadge(r.data.cart_count);
        } else {
            toast((r.data && r.data.error) || 'Could not add to cart', 'error');
        }
    } catch (e) { toast('Network error', 'error'); }
}
</script>
