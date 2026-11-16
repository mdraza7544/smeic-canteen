</main>
</div><!-- /app-main -->

<footer class="app-footer">
    &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> &middot; Developed by team SMEIC
</footer>

<div id="toast-container" class="toast-container"></div>
<div id="sidebar-backdrop" class="sidebar-backdrop hidden" style="display:none" onclick="closeSidebar()"></div>

<script src="<?= e(url('assets/js/app.js')) ?>"></script>
<script src="<?= e(url('assets/js/notifications.js')) ?>"></script>
<script>
(function () {
    function closeSidebar() {
        var sb = document.getElementById('sidebar');
        var bd = document.getElementById('sidebar-backdrop');
        if (sb) sb.classList.remove('open');
        if (bd) { bd.classList.add('hidden'); bd.style.display = 'none'; }
    }
    function openSidebar() {
        var sb = document.getElementById('sidebar');
        var bd = document.getElementById('sidebar-backdrop');
        if (sb) sb.classList.add('open');
        if (bd) { bd.classList.remove('hidden'); bd.style.display = 'block'; }
    }
    function toggleSidebar() {
        var sb = document.getElementById('sidebar');
        if (sb && sb.classList.contains('open')) closeSidebar();
        else openSidebar();
    }
    function bind() {
        var btn = document.getElementById('sidebarToggle');
        if (btn && !btn.dataset.bound) {
            btn.dataset.bound = '1';
            btn.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); toggleSidebar(); });
        }
    }
    // Expose globally
    window.closeSidebar = closeSidebar;
    window.openSidebar = openSidebar;
    window.toggleSidebar = toggleSidebar;
    // Bind on load and again after DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });
    // Re-bind if body changes (e.g. login -> dashboard navigation)
    setTimeout(bind, 50);
})();
</script>
</body>
</html>
