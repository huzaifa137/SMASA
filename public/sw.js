/*
 * SMASA site worker.
 *
 *  1. Web push (unchanged): shows notifications and opens the link on click.
 *  2. Offline pages: every page a signed-in user opens is saved on THIS device,
 *     so it still opens with no internet (overnight, a dead network, a gate
 *     with no signal). An offline page shows a bar saying when the copy was
 *     saved; /offline-pages lists everything saved. Changes cannot be saved
 *     offline: forms are blocked with a message (see partials/offline-support).
 *  3. Static files (scripts, styles, fonts, images) are cached so saved pages
 *     look right offline.
 *
 * Privacy: saved pages live only in this browser. They are wiped on logout,
 * when a different person signs in, after 14 days, and can be switched off
 * for the whole system with OFFLINE_PAGES=false.
 */

/* ---------------------------------------------------------------- push */
self.addEventListener('push', function (event) {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'SMASA Notification';
    const options = {
        body: data.body || '',
        icon: data.icon || 'https://smasa-academics.com/assets/images/brand/logo.png',
        badge: data.badge || 'https://smasa-academics.com/assets/images/brand/uplogolight.png',
        vibrate: [200, 100, 200],
        // The Laravel WebPush payload nests the link under data.url
        data: { url: (data.data && data.data.url) || data.url || '/' },
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const url = event.notification.data.url || '/';
    event.waitUntil(clients.openWindow(url));
});

/* ------------------------------------------------------- offline pages */
const PAGES = 'smasa-pages-v1';
const ASSETS = 'smasa-assets-v1';
const CONFIG = 'smasa-config-v1';
const KEEP = [PAGES, ASSETS, CONFIG];
const MAX_PAGES = 80;
const MAX_ASSETS = 500;
const MAX_AGE_MS = 14 * 24 * 3600 * 1000;
const NAV_TIMEOUT_MS = 6000;

const SCOPE_PATH = new URL(self.registration.scope).pathname.replace(/\/+$/, '');
const OFFLINE_INDEX = SCOPE_PATH + '/offline-pages';

// Requests that are signing in/out: wipe the saved pages.
const AUTH_RE = /\/(logout|user-logout|admin-logout|student-logout)\/?$/i;
// Pages that must never be saved (secrets, downloads, live endpoints).
const NEVER_RE = /\/(login|logout|user-logout|admin-logout|student-logout|password|forgot|reset|otp|download|export|token|sanctum|storage|telescope|horizon|_debugbar|passslips\/all\/file|card-scan\/(ping|offline-cards|sync|scan|logs))(\/|$|\?)|^\/api\//i;

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        // Drop caches of older versions (including the retired gate-only worker's).
        const names = await caches.keys();
        await Promise.all(names.filter((n) => !KEEP.includes(n)).map((n) => caches.delete(n)));
        await pruneOldPages();
        await self.clients.claim();
    })());
});

/* ---- settings the page tells us about (on / off, who is signed in) ---- */
self.addEventListener('message', (event) => {
    const msg = event.data || {};

    if (msg.type === 'config') {
        event.waitUntil((async () => {
            await setSetting('pages', msg.pagesEnabled ? '1' : '0');
            if (!msg.pagesEnabled) await caches.delete(PAGES);
        })());
    }

    if (msg.type === 'identity' && typeof msg.id === 'string') {
        event.waitUntil((async () => {
            const previous = await getSetting('identity');
            // Someone else signed in (or nobody did): their pages must not be visible.
            if (previous !== null && previous !== msg.id) await caches.delete(PAGES);
            await setSetting('identity', msg.id);
        })());
    }
});

async function getSetting(key) {
    const hit = await (await caches.open(CONFIG)).match('/__setting/' + key);
    return hit ? await hit.text() : null;
}
async function setSetting(key, value) {
    await (await caches.open(CONFIG)).put('/__setting/' + key, new Response(value));
}
async function pagesEnabled() {
    return (await getSetting('pages')) !== '0';
}

/* ------------------------------------------------------------- fetch */
self.addEventListener('fetch', (event) => {
    const req = event.request;
    let url;
    try { url = new URL(req.url); } catch (e) { return; }
    const sameOrigin = url.origin === self.location.origin;

    // Signing out (any method): forget every saved page, then carry on normally.
    if (sameOrigin && AUTH_RE.test(url.pathname)) {
        event.waitUntil(caches.delete(PAGES));
        return;
    }

    if (req.method !== 'GET') return;

    // The list of pages saved on this device (works with no network).
    if (sameOrigin && req.mode === 'navigate' && url.pathname.replace(/\/+$/, '') === OFFLINE_INDEX) {
        event.respondWith(offlineIndexPage(null));
        return;
    }

    if (sameOrigin && req.mode === 'navigate') {
        if (NEVER_RE.test(url.pathname + url.search)) return;
        event.respondWith(handleNavigation(event));
        return;
    }

    // Scripts, styles, fonts, images: keep a copy so saved pages still look right.
    if (['script', 'style', 'font', 'image'].includes(req.destination)) {
        if (sameOrigin && NEVER_RE.test(url.pathname)) return;
        event.respondWith(handleAsset(event));
    }
});

/* ---------------- pages ---------------- */
function timeout(ms) {
    return new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), ms));
}

function cacheable(res) {
    if (!res || res.status !== 200 || res.type !== 'basic' || res.redirected) return false;
    const type = res.headers.get('content-type') || '';
    if (type.indexOf('text/html') === -1) return false;
    if (/no-store/i.test(res.headers.get('cache-control') || '')) return false;
    return true;
}

async function storePage(key, res) {
    const html = await res.text();
    if (html.length > 3 * 1024 * 1024) return;                // huge pages are not worth keeping

    const title = (html.match(/<title[^>]*>([^<]*)/i) || [])[1] || key;
    const cache = await caches.open(PAGES);

    await cache.put(key, new Response(html, {
        status: 200,
        headers: {
            'Content-Type': 'text/html; charset=UTF-8',
            'X-SW-Cached-At': String(Date.now()),
            'X-SW-Title': encodeURIComponent(title.trim().slice(0, 120)),
        },
    }));

    const keys = await cache.keys();                           // oldest first
    for (let i = 0; i < keys.length - MAX_PAGES; i++) await cache.delete(keys[i]);
}

async function handleNavigation(event) {
    const req = event.request;
    const key = req.url;

    if (!(await pagesEnabled())) return fetch(req);

    const cache = await caches.open(PAGES);
    let cached = await cache.match(key);

    if (cached && Date.now() - Number(cached.headers.get('X-SW-Cached-At') || 0) > MAX_AGE_MS) {
        await cache.delete(key);
        cached = undefined;
    }

    const network = fetch(req).then(async (res) => {
        if (cacheable(res)) {
            // Saved in the background; the page the user sees is not delayed.
            event.waitUntil(storePage(key, res.clone()).catch(() => { }));
        }
        return res;
    });

    if (!cached) {
        try {
            return await network;
        } catch (e) {
            return offlineIndexPage(req.url);                  // never opened on this device
        }
    }

    // Have a saved copy: prefer the live page, but never wait long for a weak connection.
    try {
        return await Promise.race([network, timeout(NAV_TIMEOUT_MS)]);
    } catch (e) {
        event.waitUntil(network.catch(() => { }));             // let it finish and refresh the copy
        return withOfflineBar(cached);
    }
}

function esc(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

async function withOfflineBar(cached) {
    const html = await cached.text();
    const at = new Date(Number(cached.headers.get('X-SW-Cached-At') || Date.now()));
    const when = at.toLocaleString([], { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });

    const bar = '<div id="smasa-offline-copy" style="position:fixed;left:50%;bottom:14px;transform:translateX(-50%);'
        + 'z-index:2147483647;max-width:94vw;background:#92400e;color:#fff;border-radius:12px;padding:8px 14px;'
        + 'font:600 12.5px/1.35 system-ui,-apple-system,Segoe UI,sans-serif;box-shadow:0 6px 20px rgba(0,0,0,.3);text-align:center">'
        + 'Offline - this is the copy saved ' + esc(when) + '. Changes cannot be saved until you are back online. '
        + '<a href="' + OFFLINE_INDEX + '" style="color:#fde68a;text-decoration:underline">Saved pages</a></div>';

    const out = /<body[^>]*>/i.test(html) ? html.replace(/<body[^>]*>/i, (m) => m + bar) : bar + html;

    return new Response(out, {
        status: 200,
        headers: { 'Content-Type': 'text/html; charset=UTF-8', 'X-SW-From-Cache': '1' },
    });
}

async function offlineIndexPage(missingUrl) {
    const cache = await caches.open(PAGES);
    const rows = [];

    for (const request of await cache.keys()) {
        const hit = await cache.match(request);
        if (!hit) continue;
        rows.push({
            url: request.url,
            title: decodeURIComponent(hit.headers.get('X-SW-Title') || request.url),
            at: Number(hit.headers.get('X-SW-Cached-At') || 0),
        });
    }
    rows.sort((a, b) => b.at - a.at);

    const list = rows.length
        ? rows.map((r) => '<li><a href="' + esc(r.url) + '">' + esc(r.title) + '</a><small>'
            + esc(new Date(r.at).toLocaleString([], { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }))
            + '</small></li>').join('')
        : '<li>Nothing is saved on this device yet. Open pages while online and they will be saved here.</li>';

    const intro = missingUrl
        ? '<p>You are offline and this page has not been opened on this device before.</p>'
        : '<p>These pages are saved on this device and open without internet.</p>';

    const html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        + '<title>Saved pages</title><style>body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#f1f5f9;margin:0;padding:24px;color:#0f172a}'
        + '.box{max-width:640px;margin:0 auto;background:#fff;border-radius:16px;padding:22px 26px;box-shadow:0 4px 24px rgba(0,0,0,.08)}'
        + 'h2{margin:0 0 6px}p{color:#64748b;margin:0 0 14px}ul{list-style:none;padding:0;margin:0}'
        + 'li{padding:10px 0;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;gap:12px}li a{color:#2f2ccb;font-weight:600;text-decoration:none}'
        + 'small{color:#94a3b8;white-space:nowrap}</style></head><body><div class="box"><h2>Saved pages</h2>' + intro
        + '<ul>' + list + '</ul></div></body></html>';

    return new Response(html, { status: missingUrl ? 503 : 200, headers: { 'Content-Type': 'text/html; charset=UTF-8' } });
}

async function pruneOldPages() {
    const cache = await caches.open(PAGES);
    for (const request of await cache.keys()) {
        const hit = await cache.match(request);
        if (!hit || Date.now() - Number(hit.headers.get('X-SW-Cached-At') || 0) > MAX_AGE_MS) await cache.delete(request);
    }
}

/* ---------------- static files ---------------- */
async function handleAsset(event) {
    const req = event.request;
    const cache = await caches.open(ASSETS);
    const hit = await cache.match(req.url);
    const sameOrigin = new URL(req.url).origin === self.location.origin;

    const refresh = (sameOrigin ? fetch(req) : fetch(req.url, { mode: 'cors', credentials: 'omit' }).catch(() => fetch(req)))
        .then(async (res) => {
            const small = Number(res.headers.get('content-length') || 0) < 2 * 1024 * 1024;
            if (res && res.ok && small && (res.type === 'basic' || res.type === 'cors')) {
                await cache.put(req.url, res.clone());
                const keys = await cache.keys();
                for (let i = 0; i < keys.length - MAX_ASSETS; i++) await cache.delete(keys[i]);
            }
            return res;
        })
        .catch(() => hit || Response.error());

    if (hit) {
        event.waitUntil(refresh.catch(() => { }));             // refresh quietly for next time
        return hit;
    }
    return refresh;
}
