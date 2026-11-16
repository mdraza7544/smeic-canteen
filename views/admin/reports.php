<div class="page-header">
    <div>
        <h1 class="page-title">Reports</h1>
        <p class="page-subtitle">Download and export system reports</p>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="kpi-card kpi-indigo">
        <div class="kpi-label">Students</div>
        <div class="kpi-value"><?= e(number_format($studentsCount)) ?></div>
    </div>
    <div class="kpi-card kpi-blue">
        <div class="kpi-label">Orders</div>
        <div class="kpi-value"><?= e(number_format($ordersCount)) ?></div>
    </div>
    <div class="kpi-card kpi-emerald">
        <div class="kpi-label">Revenue</div>
        <div class="kpi-value"><?= e(money($revenue)) ?></div>
    </div>
    <div class="kpi-card kpi-amber">
        <div class="kpi-label">Transactions</div>
        <div class="kpi-value"><?= e(number_format($txCount)) ?></div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="card card-hover">
        <div class="card-body text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 flex items-center justify-center text-white mb-3">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Orders</h3>
            <p class="text-xs text-slate-500 mb-4">All orders with student and status</p>
            <div class="space-y-2">
                <a class="btn btn-primary w-full" href="<?= e(url('index.php?action=admin/export&kind=orders&format=csv')) ?>">Download CSV</a>
                <a class="btn btn-secondary w-full" href="<?= e(url('index.php?action=admin/export&kind=orders&format=pdf')) ?>">Download PDF</a>
            </div>
        </div>
    </div>
    <div class="card card-hover">
        <div class="card-body text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-emerald-500 to-cyan-500 flex items-center justify-center text-white mb-3">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Transactions</h3>
            <p class="text-xs text-slate-500 mb-4">All wallet activity</p>
            <div class="space-y-2">
                <a class="btn btn-primary w-full" href="<?= e(url('index.php?action=admin/export&kind=transactions&format=csv')) ?>">Download CSV</a>
                <a class="btn btn-secondary w-full" href="<?= e(url('index.php?action=admin/export&kind=transactions&format=pdf')) ?>">Download PDF</a>
            </div>
        </div>
    </div>
    <div class="card card-hover">
        <div class="card-body text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-amber-500 to-pink-500 flex items-center justify-center text-white mb-3">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Menu</h3>
            <p class="text-xs text-slate-500 mb-4">Menu items & prices</p>
            <div class="space-y-2">
                <a class="btn btn-primary w-full" href="<?= e(url('index.php?action=admin/export&kind=menu&format=csv')) ?>">Download CSV</a>
                <a class="btn btn-secondary w-full" href="<?= e(url('index.php?action=admin/export&kind=menu&format=pdf')) ?>">Download PDF</a>
            </div>
        </div>
    </div>
</div>
