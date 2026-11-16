<div class="page-header">
    <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Your account details</p>
    </div>
</div>
<div class="card max-w-2xl">
    <div class="card-header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-indigo-500 flex items-center justify-center text-white text-lg font-bold">
                <?= e(strtoupper(substr($parent['full_name'], 0, 1))) ?>
            </div>
            <span>Parent Details</span>
        </div>
    </div>
    <div class="card-body space-y-3 text-sm">
        <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Parent ID</span><code class="bg-slate-100 px-2 py-0.5 rounded"><?= e($parent['parent_id']) ?></code></div>
        <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Name</span><span class="font-medium"><?= e($parent['full_name']) ?></span></div>
        <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Email</span><span><?= e($parent['email']) ?></span></div>
        <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Phone</span><span><?= e($parent['phone']) ?></span></div>
        <div class="py-2"><div class="text-slate-500 mb-1">Address</div><div><?= e($parent['address']) ?></div></div>
    </div>
    <div class="card-body border-t">
        <a class="btn btn-primary" href="<?= e(url('index.php?action=change-password')) ?>">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Change Password
        </a>
    </div>
</div>
