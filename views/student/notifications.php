<div class="page-header">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Your recent updates & alerts</p>
    </div>
</div>
<?php if (empty($notes)): ?>
    <div class="card"><div class="card-body"><div class="empty-state">
        <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        No notifications yet.
    </div></div></div>
<?php else: ?>
<div class="space-y-3">
    <?php foreach ($notes as $n): ?>
        <div class="card <?= $n['is_read']?'':'border-indigo-200 bg-indigo-50/30' ?>">
            <div class="card-body py-4">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl <?= $n['is_read']?'bg-slate-100 text-slate-500':'bg-indigo-100 text-indigo-600' ?> flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <div class="font-semibold text-slate-800"><?= e($n['title']) ?></div>
                            <div class="text-xs text-slate-500 whitespace-nowrap"><?= e(dt_fmt($n['created_at'])) ?></div>
                        </div>
                        <div class="text-sm text-slate-600 mt-1"><?= e($n['message']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
