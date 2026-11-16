<div class="page-header">
    <div>
        <h1 class="page-title">Meal Preferences</h1>
        <p class="page-subtitle">Suggest items for your child today</p>
    </div>
</div>

<?php if (!empty($msg)): ?><div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800"><?= e($msg) ?></div><?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-pink-500 to-amber-500 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <span>Send a Suggestion</span>
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
                    <label class="form-label">Note (optional)</label>
                    <textarea class="form-textarea" name="note" rows="2" placeholder="e.g. Try this — it's good today!"></textarea>
                </div>
                <button class="btn btn-primary w-full !py-3">Send Suggestion</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Recent Suggestions</div>
        <div class="card-body">
            <?php if (empty($recent)): ?>
                <p class="text-slate-500 text-sm">No suggestions yet.</p>
            <?php else: foreach ($recent as $r): ?>
                <div class="flex items-center justify-between py-2.5 border-b border-slate-100 last:border-0">
                    <div>
                        <div class="font-medium text-slate-700 text-sm"><?= e($r['name']) ?></div>
                        <div class="text-xs text-slate-400"><?= e(dt_fmt($r['preference_date'] . ' 00:00:00', 'd M Y')) ?></div>
                    </div>
                    <span class="badge <?= $r['is_seen'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                        <?= $r['is_seen'] ? '✓ Seen' : 'Pending' ?>
                    </span>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
