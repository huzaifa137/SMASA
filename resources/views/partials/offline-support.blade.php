{{-- Offline pages: registers the site worker and tells it who is signed in and which
     (few) pages this person keeps offline - see config smasa.offline_page_list and
     App\Support\OfflinePages. Saved pages are wiped on logout / when someone else
     signs in (see public/sw.js). Switch the whole feature off with OFFLINE_PAGES=false. --}}
@php
    $swKeys = ['LoggedAdmin', 'LoggedTeacher', 'LoggedSchool', 'LoggedUser', 'LoggedStudent', 'ParentId'];
    $swLoggedIn = session()->hasAny($swKeys);
    $swIdentity = $swLoggedIn
        ? substr(hash_hmac('sha256', implode('|', array_map(fn($k) => (string) session($k), $swKeys)), (string) config('app.key')), 0, 24)
        : 'guest';
    $swPages = \App\Support\OfflinePages::forCurrentUser();
@endphp
<script>
    (function () {
        if (!('serviceWorker' in navigator) || !window.isSecureContext) return;

        var pagesEnabled = @json((bool) config('smasa.offline_pages', true) && $swLoggedIn && count($swPages) > 0);
        var identity = @json($swIdentity);
        var offlinePages = @json($swPages);
        var offlineVersion = @json(\App\Support\OfflinePages::version($swPages));
        var refreshHours = @json(max(1, (int) config('smasa.offline_refresh_hours', 6)));
        var offlineUrl = '/offline-pages';

        // Only fetch in the background when online and the person has not asked to save data.
        function mayFetch() {
            var c = navigator.connection;
            return navigator.onLine && !(c && c.saveData);
        }
        function sendSync(w) {
            w.postMessage({
                type: 'sync', id: identity, enabled: pagesEnabled, version: offlineVersion,
                refreshHours: refreshHours, pages: offlinePages, allowFetch: mayFetch()
            });
        }

        // The earlier gate-only worker is replaced by this site-wide one.
        navigator.serviceWorker.getRegistrations().then(function (list) {
            list.forEach(function (r) { if (/\/card-scan\/?$/.test(r.scope)) r.unregister(); });
        }).catch(function () { });

        // The small "Offline ready" badge listens to the worker's progress.
        navigator.serviceWorker.addEventListener('message', function (e) {
            if (e.data && e.data.type === 'status') updateBadge(e.data);
        });

        navigator.serviceWorker.register('/sw.js').then(function () {
            return navigator.serviceWorker.ready;
        }).then(function (reg) {
            var w = navigator.serviceWorker.controller || reg.active;
            if (w) {
                // Not on the page's critical path: wait until the browser is idle.
                var idle = window.requestIdleCallback || function (f) { setTimeout(f, 1500); };
                idle(function () { sendSync(w); });
            }

            // Keep pages fresh even when the app is closed (browsers that support it).
            if (pagesEnabled && reg.periodicSync && navigator.permissions) {
                navigator.permissions.query({ name: 'periodic-background-sync' }).then(function (p) {
                    if (p.state === 'granted') {
                        reg.periodicSync.register('smasa-refresh', { minInterval: refreshHours * 3600 * 1000 });
                    }
                }).catch(function () { });
            }

            // Ask the browser not to evict the saved pages when storage runs low.
            if (pagesEnabled && navigator.storage && navigator.storage.persist) {
                navigator.storage.persist().catch(function () { });
            }
        }).catch(function () { });

        // Connection came back: top up anything that is missing or old (the worker throttles this).
        window.addEventListener('online', function () {
            var w = navigator.serviceWorker.controller;
            if (w && pagesEnabled) w.postMessage({ type: 'precache', force: false });
        });

        // ---- small status badge: shows the offline pages are working ----
        var badge = null, badgeTimer = null;
        function updateBadge(s) {
            if (!document.body) return;
            if (!pagesEnabled || !s || !s.total) { if (badge) badge.style.display = 'none'; return; }
            if (!badge) {
                badge = document.createElement('a');
                badge.href = offlineUrl;
                badge.className = 'smasa-offline-badge';
                badge.style.cssText = 'position:fixed;left:12px;bottom:12px;z-index:1999;display:flex;align-items:center;gap:6px;'
                    + 'text-decoration:none;color:#fff;border-radius:99px;padding:5px 12px;font:600 11.5px/1.3 system-ui,-apple-system,Segoe UI,sans-serif;'
                    + 'box-shadow:0 3px 12px rgba(0,0,0,.25);opacity:.92;transition:all .3s';
                var st = document.createElement('style');
                st.textContent = '@media print{.smasa-offline-badge{display:none!important}}';
                document.head.appendChild(st);
                document.body.appendChild(badge);
            }
            var full = s.ready >= s.total;
            badge.style.display = 'flex';
            badge.style.background = s.syncing ? '#2f2ccb' : (full ? '#059669' : '#b45309');
            badge.textContent = s.syncing
                ? 'Preparing offline pages... ' + (s.done || 0) + '/' + (s.due || s.total)
                : (full ? 'Offline ready ' : 'Offline pages ') + s.ready + '/' + s.total;
            badge.title = 'Offline pages - tap to see them';

            clearTimeout(badgeTimer);
            if (!s.syncing && full) {
                // Settle down to a small dot once everything is ready.
                badgeTimer = setTimeout(function () {
                    badge.textContent = '✓';
                    badge.style.padding = '5px 9px';
                }, 6000);
            }
        }

        // ---- while offline: say so, and refuse changes that could not be saved ----
        var pill = null;
        function showPill(text, bg) {
            if (!pill) {
                pill = document.createElement('div');
                pill.style.cssText = 'position:fixed;top:12px;right:12px;z-index:2147483000;color:#fff;border-radius:99px;'
                    + 'padding:6px 14px;font:600 12px/1.3 system-ui,-apple-system,Segoe UI,sans-serif;box-shadow:0 4px 14px rgba(0,0,0,.25)';
                document.body.appendChild(pill);
            }
            pill.style.background = bg;
            pill.textContent = text;
            pill.style.display = 'block';
        }
        function goneOffline() { showPill('Offline - changes cannot be saved', '#b45309'); }
        function backOnline() {
            showPill('Back online - refresh to see the latest', '#059669');
            setTimeout(function () { if (pill) pill.style.display = 'none'; }, 6000);
        }

        window.addEventListener('offline', goneOffline);
        window.addEventListener('online', backOnline);
        if (!navigator.onLine) { document.addEventListener('DOMContentLoaded', goneOffline); }

        document.addEventListener('submit', function (e) {
            var f = e.target;
            if (navigator.onLine || !f || (f.method || 'get').toLowerCase() === 'get' || f.hasAttribute('data-offline-ok')) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            alert('You are offline, so this could not be saved. Nothing was changed. Please try again when you are back online.');
        }, true);
    })();
</script>
