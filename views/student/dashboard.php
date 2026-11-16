<div class="page-header">
    <div>
        <h1 class="page-title">Hello, <?= e($stu['full_name']) ?> 👋</h1>
        <p class="page-subtitle">Student USN: <code class="bg-slate-100 px-1.5 py-0.5 rounded"><?= e($stu['student_id']) ?></code> &middot; Department <?= e($stu['class_name']) ?></p>
    </div>
    <a href="<?= e(url('index.php?action=student/menu')) ?>" class="btn btn-primary">Browse Menu</a>
</div>

<?php if ((float)$balance < LOW_BALANCE_THRESHOLD): ?>
    <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div class="flex-1">
            <div class="font-semibold text-amber-900">Low wallet balance</div>
            <div class="text-sm text-amber-800">Your balance is <strong><?= e(money($balance)) ?></strong>. Ask your parent to transfer funds.</div>
        </div>
        <a class="btn btn-warning btn-sm" href="<?= e(url('index.php?action=student/profile')) ?>">View</a>
    </div>
<?php endif; ?>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="kpi-card kpi-indigo">
        <div class="kpi-label">Wallet</div>
        <div class="kpi-value"><?= e(money($balance)) ?></div>
        <div class="kpi-sub">Available balance</div>
    </div>
    <div class="kpi-card kpi-blue">
        <div class="kpi-label">Spent (Month)</div>
        <div class="kpi-value"><?= e(money($monthSpend)) ?></div>
        <div class="kpi-sub"><?= e(date('M Y')) ?></div>
    </div>
    <div class="kpi-card kpi-emerald">
        <div class="kpi-label">Total Orders</div>
        <div class="kpi-value"><?= e(number_format($orderCount)) ?></div>
        <div class="kpi-sub">All time</div>
    </div>
    <div class="kpi-card kpi-pink">
        <div class="kpi-label">Behavior Score</div>
        <div class="kpi-value"><?= e($stu['behavioral_score']) ?></div>
        <div class="kpi-sub">/ 100</div>
    </div>
</div>

<?php if (!empty($suggestions)): ?>
<div class="card mb-4 border-amber-200 bg-gradient-to-r from-amber-50 to-pink-50">
    <div class="card-header text-amber-900">
        <div class="flex items-center gap-2">
            <span class="text-xl">💌</span>
            <span>Parent's Suggestions for today</span>
        </div>
    </div>
    <div class="card-body grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php foreach ($suggestions as $s): ?>
            <div class="bg-white rounded-xl p-4 border border-amber-200 card-hover">
                <div class="font-semibold text-slate-800"><?= e($s['name']) ?></div>
                <div class="text-sm text-indigo-600 font-semibold num"><?= e(money((float)$s['price'])) ?></div>
                <?php if (!empty($s['note'])): ?><div class="text-sm text-amber-800 mt-2 italic">"<?= e($s['note']) ?>"</div><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($currentOrder): ?>
<div class="card mb-4">
    <div class="card-header">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span>Current Order</span>
        </div>
        <a class="text-sm text-indigo-600 hover:underline font-normal" href="<?= e(url('index.php?action=student/order-tracking&id=' . $currentOrder['id'])) ?>">Track →</a>
    </div>
    <div class="card-body">
        <div class="text-sm text-slate-500 mb-3">Order <code class="bg-slate-100 px-1.5 rounded"><?= e($currentOrder['order_number']) ?></code></div>
        <div class="timeline">
            <?php
            $steps = ['pending'=>'Pending','accepted'=>'Accepted','preparing'=>'Preparing','ready'=>'Ready','picked_up'=>'Picked Up'];
            $currentIdx = array_search($currentOrder['status'], array_keys($steps), true);
            $i = 0;
            foreach ($steps as $key => $label):
                $cls = '';
                if ($i < $currentIdx) $cls = 'done';
                elseif ($i === $currentIdx) $cls = 'done current';
            ?>
                <div class="timeline-step <?= $cls ?>">
                    <div class="dot"></div><span><?= e($label) ?></span>
                </div>
                <?php if ($i < count($steps) - 1): ?><div class="timeline-connector <?= $i < $currentIdx ? 'done' : '' ?>"></div><?php endif; ?>
            <?php $i++; endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header">Quick Links</div>
        <div class="card-body grid grid-cols-2 gap-2">
            <a class="btn btn-primary" href="<?= e(url('index.php?action=student/menu')) ?>">🍽 Menu</a>
            <a class="btn btn-secondary" href="<?= e(url('index.php?action=student/cart')) ?>">🛒 Cart</a>
            <a class="btn btn-secondary" href="<?= e(url('index.php?action=student/orders')) ?>">📋 Orders</a>
            <a class="btn btn-secondary" href="<?= e(url('index.php?action=student/token')) ?>">📱 My Token</a>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Most Ordered</div>
        <div class="card-body">
            <?php if (empty($topItems)): ?>
                <p class="text-slate-500 text-sm">No orders yet — try the menu!</p>
            <?php else: foreach ($topItems as $ti): ?>
                <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                    <span class="font-medium text-slate-700"><?= e($ti['name']) ?></span>
                    <span class="badge bg-indigo-100 text-indigo-700"><?= e($ti['qty']) ?> pcs</span>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
