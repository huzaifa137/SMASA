/*
 * Gate page service worker: lets /card-scan open with no internet.
 * Registered with scope = the card-scan page only, so it never touches the
 * site's push-notification worker (/sw.js).
 *
 *  - the page itself: network first, last good copy when offline
 *  - its scripts / styles / fonts / images: served from cache, refreshed in the background
 *  - scan, ping, sync and card-list requests are NEVER cached
 */
const CACHE = 'smasa-gate-v1';
const HUB_PATH = new URL('card-scan', self.registration.scope).pathname.replace(/\/+$/, '');

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    const isApi = url.origin === self.location.origin &&
        /\/card-scan\/(ping|offline-cards|sync|scan|logs)(\/|$)/.test(url.pathname);
    if (isApi) return;                                   // always straight to the network

    // The gate page.
    if (req.mode === 'navigate' && url.origin === self.location.origin &&
        url.pathname.replace(/\/+$/, '') === HUB_PATH) {
        event.respondWith(
            fetch(req)
                .then((res) => {
                    if (res.ok && !res.redirected) {
                        const copy = res.clone();
                        caches.open(CACHE).then((c) => c.put(HUB_PATH, copy));
                    }
                    return res;
                })
                .catch(() =>
                    caches.match(HUB_PATH).then((hit) => hit || new Response(
                        '<h2 style="font-family:sans-serif">Offline</h2><p style="font-family:sans-serif">Open the gate page once while online so it can be used without internet.</p>',
                        { status: 503, headers: { 'Content-Type': 'text/html; charset=UTF-8' } }
                    ))
                )
        );
        return;
    }

    // Things the gate page needs to draw itself.
    const fromGatePage = (req.referrer || '').indexOf(HUB_PATH) !== -1;
    const wanted = ['script', 'style', 'font', 'image'].includes(req.destination);
    if (fromGatePage && wanted) {
        event.respondWith(
            caches.open(CACHE).then((cache) =>
                cache.match(req).then((hit) => {
                    const refresh = fetch(req).then((res) => {
                        if (res && (res.ok || res.type === 'opaque')) cache.put(req, res.clone());
                        return res;
                    }).catch(() => hit);
                    return hit || refresh;
                })
            )
        );
    }
});
