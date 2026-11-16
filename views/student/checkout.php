<div class="page-header">
    <div>
        <h1 class="page-title">Confirm Your Order</h1>
        <p class="page-subtitle">Review items before placing the order</p>
    </div>
    <a class="btn btn-secondary" href="<?= e(url('index.php?action=student/cart')) ?>">← Back to cart</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 card">
        <div class="card-header">
            <span>Order Items</span>
            <span class="text-sm text-slate-500 font-normal"><?= e(count($items)) ?> item<?= count($items) !== 1 ? 's' : '' ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Item</th><th>Qty</th><th class="text-right">Price</th><th class="text-right">Subtotal</th></tr></thead>
                <tbody>
                <?php foreach ($items as $i): ?>
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="<?= e(menu_image($i['image'])) ?>" class="w-10 h-10 rounded-xl object-cover" alt="">
                                <div>
                                    <div class="font-medium text-slate-800"><?= e($i['name']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-slate-100 text-slate-700">×<?= e($i['quantity']) ?></span></td>
                        <td class="text-right num"><?= e(money((float)$i['price'])) ?></td>
                        <td class="text-right font-semibold num"><?= e(money((float)$i['price'] * (int)$i['quantity'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-semibold">
                        <td colspan="3" class="text-right">Total Amount</td>
                        <td class="text-right text-indigo-700 num text-lg"><?= e(money($total)) ?></td>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Payment &amp; Confirm</div>
        <div class="card-body">
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Wallet Balance</span><span class="font-semibold num text-emerald-700"><?= e(money($balance)) ?></span></div>
                <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Order Total</span><span class="font-semibold num"><?= e(money($total)) ?></span></div>
                <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">After Payment</span><span class="font-semibold num <?= ($balance - $total) < 0 ? 'text-red-600' : 'text-slate-800' ?>"><?= e(money($balance - $total)) ?></span></div>
            </div>
            <?php if ($balance < $total): ?>
                <div class="mt-3 p-3 rounded-lg bg-red-50 text-red-700 text-sm">⚠ Insufficient balance. Please ask your parent to top up first.</div>
                <button class="btn btn-secondary w-full !py-3 mt-3 opacity-50 cursor-not-allowed" disabled>Insufficient balance</button>
            <?php else: ?>
                <form method="post" action="<?= e(url('index.php?action=student/checkout')) ?>" class="mt-4">
                    <?= csrf_field() ?>
                    <button class="btn btn-primary w-full !py-3 text-base" onclick="return confirmAction('Place this order? Funds will be deducted from your wallet.')">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Confirm &amp; Place Order
                    </button>
                </form>
                <p class="text-xs text-slate-500 mt-2 text-center">By placing the order you agree to deduct <?= e(money($total)) ?> from your wallet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
