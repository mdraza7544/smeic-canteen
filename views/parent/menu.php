<div class="page-header">
    <div>
        <h1 class="page-title">Browse Menu</h1>
        <p class="page-subtitle">See what's available & suggest to your child</p>
    </div>
    <a class="btn btn-secondary" href="<?= e(url('index.php?action=parent/preferences')) ?>">My Suggestions</a>
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
            <div class="text-2xl font-extrabold"><?= e(money((float)$special['price'])) ?></div>
        </div>
    </div>
<?php endif; ?>

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
                <form method="post" action="<?= e(url('index.php?action=parent/preferences')) ?>" class="mt-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="menu_item_id" value="<?= e($it['id']) ?>">
                    <button class="btn btn-secondary btn-sm w-full">💌 Suggest to my child</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
