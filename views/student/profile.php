<div class="page-header">
    <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Your account details</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 flex items-center justify-center text-white text-lg font-bold">
                    <?= e(strtoupper(substr($stu['full_name'], 0, 1))) ?>
                </div>
                <span>Student Details</span>
            </div>
        </div>
        <div class="card-body space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Student ID</span><span><code class="bg-slate-100 px-2 py-0.5 rounded"><?= e($stu['student_id']) ?></code></span></div>
            <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Name</span><span class="font-medium"><?= e($stu['full_name']) ?></span></div>
            <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Class</span><span><?= e($stu['class_name']) ?></span></div>
            <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Roll No</span><span><?= e($stu['roll_no']) ?></span></div>
            <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Behavior Score</span><span class="font-semibold text-amber-600"><?= e($stu['behavioral_score']) ?> / 100</span></div>
            <div class="py-2"><div class="text-slate-500 mb-1">Address</div><div><?= e($stu['address']) ?></div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Parent / Guardian</div>
        <div class="card-body space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Parent ID</span><span><code class="bg-slate-100 px-2 py-0.5 rounded"><?= e($stu['parent_code'] ?? '-') ?></code></span></div>
            <div class="flex justify-between py-2 border-b border-slate-100"><span class="text-slate-500">Name</span><span class="font-medium"><?= e($stu['parent_name'] ?? '-') ?></span></div>
            <div class="flex justify-between py-2"><span class="text-slate-500">Phone</span><span><?= e($stu['parent_phone'] ?? '-') ?></span></div>
        </div>
    </div>

    <div class="card md:col-span-2">
        <div class="card-header">Quick Actions</div>
        <div class="card-body flex flex-wrap gap-2">
            <a class="btn btn-primary" href="<?= e(url('index.php?action=change-password')) ?>">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Change Password
            </a>
            <a class="btn btn-secondary" href="<?= e(url('index.php?action=student/token')) ?>">📱 My Token</a>
            <a class="btn btn-secondary" href="<?= e(url('index.php?action=student/feedback')) ?>">💬 Send Feedback</a>
        </div>
    </div>
</div>
