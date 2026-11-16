<div class="page-header">
    <div>
        <h1 class="page-title">Menu Items</h1>
        <p class="page-subtitle">Read-only view of all menu items managed by the canteen</p>
    </div>
</div>

<div class="menu-grid">
    <?php if (empty($items)): ?>
        <div class="col-span-full empty-state">No menu items yet.</div>
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
                <div class="mt-2 flex items-center justify-between">
                    <span class="badge <?= $i['is_available']?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-700' ?>">
                        <?= $i['is_available']?'● Available':'● Unavailable' ?>
                    </span>
                    <?php
                        $sp = db()->prepare("SELECT * FROM daily_specials WHERE menu_item_id=? AND is_active=1 AND expires_at > NOW()");
                        $sp->execute([$i['id']]);
                        if ($sp->fetch()) echo '<span class="badge bg-amber-100 text-amber-800">★ Special</span>';
                    ?>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>
