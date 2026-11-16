// Notifications polling + pickup reminder polling.
(function () {
    'use strict';
    const userId = document.body.dataset.userId;
    const userType = document.body.dataset.userType;
    if (!userId || !userType) return;

    // 10s notification poll
    setInterval(async () => {
        try {
            const r = await smeicAjax('api/check-notifications.php');
            if (r.data && r.data.unread > 0 && r.data.latest) {
                if (!window.__lastNotifId || r.data.latest.id > window.__lastNotifId) {
                    window.__lastNotifId = r.data.latest.id;
                    toast(r.data.latest.title + ' — ' + r.data.latest.message, 'info', 5000);
                }
                const badge = document.querySelector('[data-notif-badge]');
                if (badge) {
                    badge.textContent = r.data.unread;
                    badge.classList.remove('hidden');
                }
            } else if (r.data && r.data.unread === 0) {
                const badge = document.querySelector('[data-notif-badge]');
                if (badge) badge.classList.add('hidden');
            }
        } catch (e) { /* silent */ }
    }, 10000);

    // 60s pickup reminder poll — student & canteen
    if (userType === 'student' || userType === 'canteen') {
        setInterval(async () => {
            try { await smeicAjax('api/check-pickup-reminders.php'); } catch (e) {}
        }, 60000);
    }
})();
