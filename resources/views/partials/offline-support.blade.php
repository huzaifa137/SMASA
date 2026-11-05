{{-- Offline pages: registers the site worker and tells it who is signed in.
     Saved pages are wiped on logout / when someone else signs in (see public/sw.js).
     Switch the whole feature off with OFFLINE_PAGES=false in .env. --}}
@php
    $swKeys = ['LoggedAdmin', 'LoggedTeacher', 'LoggedSchool', 'LoggedUser', 'LoggedStudent', 'ParentId'];
    $swLoggedIn = session()->hasAny($swKeys);
    $swIdentity = $swLoggedIn
        ? substr(hash_hmac('sha256', implode('|', array_map(fn($k) => (string) session($k), $swKeys)), (string) config('app.key')), 0, 24)
        : 'guest';
@endphp
<script>
    (function () {
        if (!('serviceWorker' in navigator) || !window.isSecureContext) return;

        var pagesEnabled = @json((bool) config('smasa.offline_pages', true) && $swLoggedIn);
        var identity = @json($swIdentity);

        // The earlier gate-only worker is replaced by this site-wide one.
        navigator.serviceWorker.getRegistrations().then(function (list) {
            list.forEach(function (r) { if (/\/card-scan\/?$/.test(r.scope)) r.unregister(); });
        }).catch(function () { });

        navigator.serviceWorker.register('/sw.js').then(function () {
            return navigator.serviceWorker.ready;
        }).then(function (reg) {
            var w = navigator.serviceWorker.controller || reg.active;
            if (w) {
                w.postMessage({ type: 'config', pagesEnabled: pagesEnabled });
                w.postMessage({ type: 'identity', id: identity });
            }
        }).catch(function () { });

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
