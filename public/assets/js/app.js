// SMEIC University Canteen shared JS — sidebar toggle, toasts, AJAX helper, password eye toggle, cart, utilities.
(function () {
    'use strict';

    // ----- Sidebar toggle (real handler is in footer.php) -----

    // ----- CSRF token (from <meta> tag in header.php) -----
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ----- Generic AJAX wrapper -----
    window.smeicAjax = async function (url, options = {}) {
        options.headers = Object.assign({
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrf,
        }, options.headers || {});

        if (options.json !== undefined) {
            options.body = JSON.stringify(options.json);
            options.headers['Content-Type'] = 'application/json';
            options.method = options.method || 'POST';
            delete options.json;
        }
        if (!options.method && options.body) options.method = 'POST';

        const res = await fetch(url, options);
        let data = null;
        try { data = await res.json(); } catch (_) { data = { ok: res.ok, raw: true }; }
        return { status: res.status, data: data || { ok: res.ok } };
    };

    // ----- Toast -----
    window.toast = function (msg, type = 'info', ms = 3500) {
        const c = document.getElementById('toast-container');
        if (!c) return;
        const t = document.createElement('div');
        t.className = 'toast toast-' + type;
        t.textContent = msg;
        c.appendChild(t);
        setTimeout(() => {
            t.style.transition = 'opacity 0.4s';
            t.style.opacity = '0';
            setTimeout(() => t.remove(), 400);
        }, ms);
    };

    // ----- Confirm helper -----
    window.confirmAction = function (msg) { return window.confirm(msg); };

    // ----- Auto-dismiss flash messages -----
    setTimeout(() => {
        document.querySelectorAll('.flash-msg').forEach(el => {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        });
    }, 4500);

    // ----- Password show/hide eye toggle -----
    // Wrap any <input type="password"> in a relative container with a button.
    function setupPasswordToggle() {
        document.querySelectorAll('input[type="password"]').forEach(input => {
            if (input.dataset.eyeInit === '1') return;
            input.dataset.eyeInit = '1';

            // Skip if already inside a wrapper
            if (input.parentElement?.classList.contains('password-wrapper')) return;

            const wrapper = document.createElement('div');
            wrapper.className = 'password-wrapper relative';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            input.classList.add('!pr-11');

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'password-toggle';
            btn.setAttribute('aria-label', 'Show password');
            btn.setAttribute('tabindex', '-1');
            btn.innerHTML = `
                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <svg class="eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            `;
            wrapper.appendChild(btn);

            btn.addEventListener('click', function () {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                btn.querySelector('.eye-open').classList.toggle('hidden', !isPassword);
                btn.querySelector('.eye-closed').classList.toggle('hidden', isPassword);
                btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                input.focus();
                const v = input.value; input.value = ''; input.value = v; // keep cursor
            });
        });
    }
    setupPasswordToggle();
    // Re-run in case views add inputs dynamically
    document.addEventListener('DOMContentLoaded', setupPasswordToggle);
    setTimeout(setupPasswordToggle, 100);

    // ----- Cart helpers (used on student pages) -----
    window.smeicCart = {
        add: async function (itemId, qty) {
            const fd = new FormData();
            fd.append('item_id', itemId);
            fd.append('quantity', qty);
            fd.append('csrf_token', csrf);
            return smeicAjax('api/update-cart.php', { body: fd, method: 'POST' });
        },
        updateBadge: function (count) {
            const badge = document.querySelector('[data-cart-badge]');
            if (!badge) return;
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    };

    // ----- Notification helpers -----
    window.smeicNotif = {
        markRead: async function (id) {
            return smeicAjax('api/mark-notification-read.php', { json: { id: id || 0 } });
        }
    };

    // ----- Sidebar settings accordion -----
    function setupSidebarSettings() {
        const groups = document.querySelectorAll('[data-settings-group]');
        groups.forEach(group => {
            const btn = group.querySelector('.sidebar-settings-toggle');
            const panel = group.querySelector('.sidebar-settings-panel');
            if (!btn || !panel) return;

            const key = 'smeic.sidebar.settings.' + (document.body.dataset.userType || 'guest');
            const saved = localStorage.getItem(key);
            const expanded = saved === null ? btn.getAttribute('aria-expanded') === 'true' : saved === '1';

            const applyState = (open) => {
                group.classList.toggle('open', open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                panel.style.maxHeight = open ? panel.scrollHeight + 'px' : '0px';
                localStorage.setItem(key, open ? '1' : '0');
            };

            applyState(expanded);
            btn.addEventListener('click', () => applyState(btn.getAttribute('aria-expanded') !== 'true'));
            btn.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    applyState(btn.getAttribute('aria-expanded') !== 'true');
                }
            });

            window.addEventListener('resize', () => {
                if (btn.getAttribute('aria-expanded') === 'true') {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                }
            });
        });
    }
    document.addEventListener('DOMContentLoaded', setupSidebarSettings);
})();
