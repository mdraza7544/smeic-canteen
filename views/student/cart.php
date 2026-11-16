<div class="page-header">
    <div>
        <h1 class="page-title">My Cart</h1>
        <p class="page-subtitle">Review your items and place the order</p>
    </div>
    <a class="btn btn-secondary" href="<?= e(url('index.php?action=student/menu')) ?>">← Continue browsing</a>
</div>

<?php if (empty($items)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Your cart is empty.
                <div class="mt-3">
                    <a class="btn btn-primary" href="<?= e(url('index.php?action=student/menu')) ?>">Browse the menu</a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Item</th><th class="text-right">Price</th><th>Quantity</th><th class="text-right">Subtotal</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($items as $ci): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="<?= e(menu_image($ci['image'])) ?>" class="w-12 h-12 rounded-xl object-cover" alt="">
                            <div>
                                <div class="font-medium text-slate-800"><?= e($ci['name']) ?></div>
                                <?php if ((int)$ci['is_available'] !== 1): ?>
                                    <div class="text-xs text-red-600">⚠ Currently unavailable</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="text-right num"><?= e(money((float)$ci['price'])) ?></td>
                    <td>
                        <div class="inline-flex items-center border border-slate-200 rounded-lg overflow-hidden">
                            <button class="px-2 py-1 bg-slate-50 hover:bg-slate-100" onclick="changeQty(<?= e($ci['menu_item_id']) ?>, <?= e($ci['quantity'] - 1) ?>)">−</button>
                            <span class="px-3 text-sm font-medium"><?= e($ci['quantity']) ?></span>
                            <button class="px-2 py-1 bg-slate-50 hover:bg-slate-100" onclick="changeQty(<?= e($ci['menu_item_id']) ?>, <?= e($ci['quantity'] + 1) ?>)">+</button>
                        </div>
                    </td>
                    <td class="text-right font-semibold num"><?= e(money((float)$ci['price'] * (int)$ci['quantity'])) ?></td>
                    <td class="text-right">
                        <button class="btn btn-danger btn-sm" onclick="removeFromCart(<?= e($ci['menu_item_id']) ?>)">Remove</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
    <div class="md:col-span-2 card">
        <div class="card-body">
            <div class="flex items-center justify-between mb-2">
                <span class="text-slate-500">Subtotal</span>
                <span class="font-semibold num"><?= e(money($total)) ?></span>
            </div>
            <div class="flex items-center justify-between text-lg font-bold border-t border-slate-100 pt-2">
                <span>Total</span>
                <span class="text-indigo-700 num"><?= e(money($total)) ?></span>
            </div>
            <div class="mt-3 text-sm text-slate-500">Wallet balance: <strong class="<?= $balance < $total ? 'text-red-600' : 'text-emerald-700' ?> num"><?= e(money($balance)) ?></strong></div>
            <?php if ($balance < $total): ?>
                <div class="mt-3 p-2 rounded-lg bg-red-50 text-red-700 text-sm">⚠ Insufficient balance. Ask your parent to top up.</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-body flex flex-col justify-between h-full">
            <div>
                <h3 class="font-bold text-slate-800 mb-1">Ready to order?</h3>
                <p class="text-xs text-slate-500">Funds will be deducted from your wallet.</p>
            </div>
            <a href="<?= e(url('index.php?action=student/checkout')) ?>" class="btn btn-primary w-full !py-3 text-base mt-4 <?= $balance < $total ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' ?>">
                <?= $balance < $total ? 'Insufficient balance' : 'Proceed to Confirm →' ?>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
async function changeQty(itemId, qty) {
    const r = await smeicCart.add(itemId, qty);
    if (r.data && r.data.ok) location.reload();
    else toast((r.data && r.data.error) || 'Failed', 'error');
}
async function removeFromCart(itemId) {
    if (!confirmAction('Remove this item?')) return;
    const r = await smeicCart.add(itemId, 0);
    if (r.data && r.data.ok) location.reload();
    else toast((r.data && r.data.error) || 'Failed', 'error');
}
</script>
