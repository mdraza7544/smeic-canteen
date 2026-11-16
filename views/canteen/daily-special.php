<div class="page-header">
    <div>
        <h1 class="page-title">Daily Specials</h1>
        <p class="page-subtitle">Feature one item as today's pick</p>
    </div>
</div>

<?php if (!empty($msg)): ?><div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800"><?= e($msg) ?></div><?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-pink-500 flex items-center justify-center text-white text-xl">★</div>
                <span>Set Today's Special</span>
            </div>
        </div>
        <div class="card-body">
            <form method="post" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="form-label">Menu Item</label>
                    <select class="form-select" name="menu_item_id" required>
                        <option value="">— Select —</option>
                        <?php foreach ($items as $i): ?>
                            <option value="<?= e($i['id']) ?>"><?= e($i['name']) ?> (<?= e(money((float)$i['price'])) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Expires at</label>
                    <input type="datetime-local" class="form-input" name="expires_at" value="<?= e(date('Y-m-d\TH:i', strtotime('+1 day'))) ?>">
                </div>
                <button class="btn btn-primary w-full !py-3">Set as Today's Special</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Currently Active</div>
        <div class="card-body">
            <?php if (!$current): ?>
                <div class="empty-state">
                    <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.36-2.448a1 1 0 00-1.175 0l-3.371 2.448c-.784.57-1.838-.197-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                    No active special right now.
                </div>
            <?php else: ?>
                <div class="flex items-center gap-3">
                    <img src="<?= e(menu_image($current['image'])) ?>" class="w-20 h-20 rounded-xl object-cover ring-2 ring-amber-200" alt="">
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-slate-800"><?= e($current['name']) ?></div>
                        <div class="text-sm text-slate-500 line-clamp-2"><?= e($current['description']) ?></div>
                        <div class="text-xs text-amber-700 mt-1">Expires: <?= e(dt_fmt($current['expires_at'])) ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
