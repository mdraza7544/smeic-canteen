<div class="page-header">
    <div>
        <h1 class="page-title"><?= $type==='order' ? 'Pickup Token' : 'My Token' ?></h1>
        <p class="page-subtitle"><?= $type==='order' ? 'Show this code to the canteen staff' : 'Your student identity code' ?></p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="card">
        <div class="card-header">
            <span><?= $type==='order' ? 'Pickup Token' : 'Student Token' ?></span>
            <?php if ($type === 'order' && $order && $order['status'] === 'ready' && !$tokenExpired): ?>
                <span class="badge badge-ready">Active</span>
            <?php elseif ($type === 'order' && $tokenExpired): ?>
                <span class="badge badge-cancelled">Expired</span>
            <?php endif; ?>
        </div>
        <div class="card-body text-center py-10">
            <?php if ($type === 'order'): ?>
                <?php if ($tokenExpired): ?>
                    <div class="mb-4">
                        <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold text-red-600">Token Expired</div>
                    <p class="text-sm text-slate-500 mt-1">This token has expired after <?= e(TOKEN_EXPIRY_HOURS) ?> hours. A new token will be generated for your next order.</p>
                <?php elseif ($order && $order['status'] === 'ready'): ?>
                    <div class="mb-3">
                        <div class="text-xs uppercase tracking-wider text-slate-500 mb-2">Your 6-Digit Token</div>
                        <div class="token-display" id="tokenDisplay">
                            <?php for ($i = 0; $i < strlen($token); $i++): ?>
                                <span class="token-digit"><?= e($token[$i]) ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 border border-emerald-200">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm font-medium text-emerald-700">Expires in <?= e($remainingTime) ?></span>
                    </div>
                    <div class="mt-3 text-sm text-slate-500">Show this code to the canteen staff to collect your order.</div>
                <?php elseif ($order && in_array($order['status'], ['pending','accepted','preparing'], true)): ?>
                    <div class="mb-4">
                        <div class="w-16 h-16 mx-auto rounded-full bg-amber-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold text-amber-600">Preparing Your Order</div>
                    <div class="token-display token-display-dimmed mt-3">
                        <?php for ($i = 0; $i < strlen($token); $i++): ?>
                            <span class="token-digit"><?= e($token[$i]) ?></span>
                        <?php endfor; ?>
                    </div>
                    <p class="text-sm text-slate-500 mt-2">Token will be active once order is ready.</p>
                <?php else: ?>
                    <div class="text-slate-400 text-sm">No active token.</div>
                <?php endif; ?>
            <?php else: ?>
                <div class="mb-3">
                    <div class="text-xs uppercase tracking-wider text-slate-500 mb-2">Student Identity</div>
                    <div class="text-3xl font-extrabold text-indigo-700 font-mono tracking-widest"><?= e($token) ?></div>
                </div>
                <div class="mt-3 text-sm text-slate-500">Your Student ID for identification.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Student Details</div>
        <div class="card-body space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Name</span><span class="font-medium"><?= e($stu['full_name']) ?></span></div>
            <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Student ID</span><span><code class="bg-slate-100 px-2 py-0.5 rounded"><?= e($stu['student_id']) ?></code></span></div>
            <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Class</span><span><?= e($stu['class_name']) ?></span></div>
            <div class="flex justify-between py-2"><span class="text-slate-500">Roll No</span><span><?= e($stu['roll_no']) ?></span></div>
        </div>
    </div>
</div>

<?php if ($type === 'order' && $order && $order['status'] === 'ready' && !$tokenExpired): ?>
<script>
setInterval(() => {
    location.reload();
}, 60000);
</script>
<?php endif; ?>
