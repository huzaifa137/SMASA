/*
 * SMASA site worker.
 *
 *  1. Web push (unchanged): shows notifications and opens the link on click.
 *  2. Offline pages: a SHORT list of pages (config smasa.offline_page_list, only
 *     those the signed-in user may open) is kept on THIS device so it still opens
 *     with no internet. Each is saved when visited and also saved quietly in the
 *     background if never visited (throttled). /offline-pages shows them by name
 *     with their status. Changes cannot be saved offline: forms are blocked with
 *     a message (see partials/offline-support).
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
/*
 * Only the pages listed by the server (config smasa.offline_page_list, filtered
 * by what the signed-in user may open) are kept. They are saved when visited AND
 * saved quietly in the background even if never visited. The background save is
 * throttled: a page is re-fetched only when it is missing or older than the
 * refresh window, at most one page at a time, only while online, and a failed
 * page is not retried for 30 minutes.
 */
const PAGES = 'smasa-pages-v2';
const ASSETS = 'smasa-assets-v2';
const CONFIG = 'smasa-config-v2';
const KEEP = [PAGES, ASSETS, CONFIG];
const MAX_ASSETS = 300;
const MAX_AGE_MS = 14 * 24 * 3600 * 1000;
const NAV_TIMEOUT_MS = 6000;
const PRECACHE_TIMEOUT_MS = 20000;
const PRECACHE_GAP_MS = 400;
const RETRY_MS = 30 * 60 * 1000;
const DEFAULT_REFRESH_MS = 6 * 3600 * 1000;
const ASSETS_PER_PAGE = 40;

const SCOPE_PATH = new URL(self.registration.scope).pathname.replace(/\/+$/, '');
const OFFLINE_INDEX = SCOPE_PATH + '/offline-pages';

// Requests that are signing in/out: wipe the saved pages.
const AUTH_RE = /\/(logout|user-logout|admin-logout|student-logout)\/?$/i;
// Pages that must never be saved (secrets, downloads, live endpoints).
const NEVER_RE = /\/(login|logout|user-logout|admin-logout|student-logout|password|forgot|reset|otp|download|export|token|sanctum|storage|telescope|horizon|_debugbar|passslips\/all\/file|card-scan\/(ping|offline-cards|sync|scan|logs))(\/|$|\?)|^\/api\//i;

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        // Drop caches of older versions (the earlier "save everything" worker's included).
        const names = await caches.keys();
        await Promise.all(names.filter((n) => !KEEP.includes(n)).map((n) => caches.delete(n)));
        await pruneOldPages();
        await self.clients.claim();
    })());
});

/* ------------------------------------------------ small helpers */
function sleep(ms) { return new Promise((r) => setTimeout(r, ms)); }
function timeout(ms) { return new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), ms)); }
function esc(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

// One saved copy per page: origin + path (no query string, no trailing slash).
function pageKey(url) {
    const u = new URL(url, self.location.origin);
    return u.origin + (u.pathname.replace(/\/+$/, '') || '/');
}

async function getSetting(key) {
    const hit = await (await caches.open(CONFIG)).match('/__setting/' + key);
    return hit ? await hit.text() : null;
}
async function setSetting(key, value) {
    await (await caches.open(CONFIG)).put('/__setting/' + key, new Response(String(value)));
}
async function getJSON(key, fallback) {
    try { const v = await getSetting(key); return v === null ? fallback : JSON.parse(v); } catch (e) { return fallback; }
}
async function setJSON(key, value) { await setSetting(key, JSON.stringify(value)); }

async function getManifest() {
    const m = await getJSON('manifest', null);
    return m && Array.isArray(m.pages) ? m : { version: '', pages: [], refreshMs: DEFAULT_REFRESH_MS };
}

// Work that changes saved pages runs one job at a time.
let chain = Promise.resolve();
function enqueue(job) {
    chain = chain.catch(() => { }).then(job);
    return chain;
}

/* ------------------------------------- messages from the pages */
self.addEventListener('message', (event) => {
    const msg = event.data || {};

    if (msg.type === 'sync') {
        event.waitUntil(enqueue(() => handleSync(msg)));
    } else if (msg.type === 'precache') {
        event.waitUntil(enqueue(() => precache({ force: !!msg.force })));
    } else if (msg.type === 'status') {
        event.waitUntil((async () => {
            const status = await buildStatus();
            if (event.source) event.source.postMessage(status); else await broadcast(status);
        })());
    }
});

// The page tells us who is signed in and which pages that person keeps offline.
async function handleSync(msg) {
    const previous = await getSetting('identity');
    if (previous !== null && previous !== msg.id) {
        // Someone else signed in: their pages must not be visible to this person.
        await caches.delete(PAGES);
        await setJSON('tried', {});
    }
    await setSetting('identity', msg.id);

    const pages = Array.isArray(msg.pages) ? msg.pages : [];
    if (!msg.enabled || !pages.length) {
        await caches.delete(PAGES);
        await setJSON('manifest', { version: '', pages: [], refreshMs: DEFAULT_REFRESH_MS });
        await broadcast(await buildStatus());
        return;
    }

    const before = await getManifest();
    await setJSON('manifest', {
        version: String(msg.version || ''),
        refreshMs: Math.max(1, Number(msg.refreshHours) || 6) * 3600 * 1000,
        pages: pages.map((p) => ({
            key: String(p.key), label: String(p.label), desc: String(p.desc || ''),
            icon: String(p.icon || ''), path: String(p.path),
        })),
    });

    await pruneToManifest();
    const changed = before.version !== String(msg.version || '');
    if (msg.allowFetch) await precache({ force: false, retryNow: changed });
    else await broadcast(await buildStatus());
}

// Anything saved that is not on the list (e.g. from the older save-everything worker) goes.
async function pruneToManifest() {
    const m = await getManifest();
    const allowed = new Set(m.pages.map((p) => pageKey(p.path)));
    const cache = await caches.open(PAGES);
    for (const request of await cache.keys()) {
        if (!allowed.has(pageKey(request.url))) await cache.delete(request);
    }
}

async function wipeAll() {
    await caches.delete(PAGES);
    await setJSON('manifest', { version: '', pages: [], refreshMs: DEFAULT_REFRESH_MS });
    await setJSON('tried', {});
    await broadcast(await buildStatus());
}

/* ------------------------------------------------------------- fetch */
self.addEventListener('fetch', (event) => {
    const req = event.request;
    let url;
    try { url = new URL(req.url); } catch (e) { return; }
    const sameOrigin = url.origin === self.location.origin;

    // Signing out (any method): forget every saved page, then carry on normally.
    if (sameOrigin && AUTH_RE.test(url.pathname)) {
        event.waitUntil(wipeAll());
        return;
    }

    if (req.method !== 'GET') return;

    // The list of offline pages (works with no network).
    if (sameOrigin && req.mode === 'navigate' && url.pathname.replace(/\/+$/, '') === OFFLINE_INDEX) {
        event.respondWith((async () => offlineIndexPage(null, await buildStatus()))());
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
function cacheable(res) {
    if (!res || res.status !== 200 || res.type !== 'basic' || res.redirected) return false;
    const type = res.headers.get('content-type') || '';
    if (type.indexOf('text/html') === -1) return false;
    if (/no-store/i.test(res.headers.get('cache-control') || '')) return false;
    return true;
}

// Saves the page and returns its HTML (so its scripts / styles can be saved too).
async function storePage(key, res) {
    const html = await res.text();
    if (html.length > 3 * 1024 * 1024) return null;           // huge pages are not worth keeping

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
    return html;
}

async function handleNavigation(event) {
    const req = event.request;
    const url = new URL(req.url);
    const key = pageKey(req.url);

    const manifest = await getManifest();
    const listed = url.search === '' && manifest.pages.some((p) => pageKey(p.path) === key);

    // Not one of the offline pages: behave like a normal website.
    if (!listed) {
        try {
            return await fetch(req);
        } catch (e) {
            return offlineIndexPage(req.url, await buildStatus());
        }
    }

    const cache = await caches.open(PAGES);
    let cached = await cache.match(key);

    if (cached && Date.now() - Number(cached.headers.get('X-SW-Cached-At') || 0) > MAX_AGE_MS) {
        await cache.delete(key);
        cached = undefined;
    }

    const network = fetch(req).then(async (res) => {
        if (cacheable(res)) {
            // Saved in the background; the page the user sees is not delayed.
            event.waitUntil(storePage(key, res.clone()).then(() => broadcast()).catch(() => { }));
        }
        return res;
    });

    if (!cached) {
        try {
            return await network;
        } catch (e) {
            return offlineIndexPage(req.url, await buildStatus());   // listed, but never saved on this device
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

async function withOfflineBar(cached) {
    const html = await cached.text();
    const at = new Date(Number(cached.headers.get('X-SW-Cached-At') || Date.now()));
    const when = at.toLocaleString([], { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });

    const bar = '<div id="smasa-offline-copy" style="position:fixed;left:50%;bottom:14px;transform:translateX(-50%);'
        + 'z-index:2147483647;max-width:94vw;background:#92400e;color:#fff;border-radius:12px;padding:8px 14px;'
        + 'font:600 12.5px/1.35 system-ui,-apple-system,Segoe UI,sans-serif;box-shadow:0 6px 20px rgba(0,0,0,.3);text-align:center">'
        + 'Offline - this is the copy saved ' + esc(when) + '. Changes cannot be saved until you are back online. '
        + '<a href="' + OFFLINE_INDEX + '" style="color:#fde68a;text-decoration:underline">Offline pages</a></div>';

    const out = /<body[^>]*>/i.test(html) ? html.replace(/<body[^>]*>/i, (m) => m + bar) : bar + html;

    return new Response(out, {
        status: 200,
        headers: { 'Content-Type': 'text/html; charset=UTF-8', 'X-SW-From-Cache': '1' },
    });
}

async function pruneOldPages() {
    const cache = await caches.open(PAGES);
    for (const request of await cache.keys()) {
        const hit = await cache.match(request);
        if (!hit || Date.now() - Number(hit.headers.get('X-SW-Cached-At') || 0) > MAX_AGE_MS) await cache.delete(request);
    }
}

/* ------------- saving the listed pages in the background ------------- */
// 'ok' saved | 'http' server answered but it is not a savable page | 'network' no connection
async function fetchAndStore(page) {
    const url = new URL(page.path, self.location.origin).href;
    let res;
    try {
        res = await Promise.race([
            fetch(url, { credentials: 'same-origin', cache: 'no-store', headers: { 'X-SMASA-Prefetch': '1' } }),
            timeout(PRECACHE_TIMEOUT_MS),
        ]);
    } catch (e) {
        return 'network';
    }
    if (!cacheable(res)) return 'http';                        // e.g. redirected to login, 403, error page

    const html = await storePage(pageKey(url), res);
    if (html) await precacheAssets(html, url);
    return html ? 'ok' : 'http';
}

async function precache(opts) {
    const force = !!(opts && opts.force);
    const manifest = await getManifest();
    if (!manifest.pages.length) { await broadcast(await buildStatus()); return; }

    const cache = await caches.open(PAGES);
    const tried = await getJSON('tried', {});
    const refreshMs = manifest.refreshMs || DEFAULT_REFRESH_MS;
    const now = Date.now();
    const retryNow = force || !!(opts && opts.retryNow);

    const due = [];
    for (const p of manifest.pages) {
        const hit = await cache.match(pageKey(p.path));
        const savedAt = hit ? Number(hit.headers.get('X-SW-Cached-At') || 0) : 0;
        const stale = !hit || now - savedAt > refreshMs;
        const coolingDown = now - (tried[p.key] || 0) < RETRY_MS;
        if (force || (stale && (retryNow || !coolingDown))) due.push(p);
    }

    if (!due.length) { await broadcast(await buildStatus()); return; }

    let done = 0;
    await broadcast(await buildStatus({ syncing: true, done, due: due.length }));

    for (const p of due) {
        const result = await fetchAndStore(p);
        if (result === 'network') break;                       // offline: stop quietly, try again later
        tried[p.key] = Date.now();
        done++;
        await broadcast(await buildStatus({ syncing: done < due.length, done, due: due.length }));
        if (done < due.length) await sleep(PRECACHE_GAP_MS);
    }

    await setJSON('tried', tried);
    if (done) await setSetting('syncedAt', Date.now());
    await broadcast(await buildStatus());
}

// Also save the scripts and styles a page needs, so a page never opened still works offline.
async function precacheAssets(html, pageUrl) {
    const found = new Set();
    let m;
    const scriptRe = /<script[^>]+src=["']([^"']+)["']/gi;
    while ((m = scriptRe.exec(html))) found.add(m[1]);
    const linkRe = /<link\b[^>]*>/gi;
    while ((m = linkRe.exec(html))) {
        if (!/rel=["']?stylesheet/i.test(m[0])) continue;
        const href = (m[0].match(/href=["']([^"']+)["']/i) || [])[1];
        if (href) found.add(href);
    }

    const cache = await caches.open(ASSETS);
    let fetched = 0;
    for (const raw of found) {
        if (fetched >= ASSETS_PER_PAGE) break;
        let u;
        try { u = new URL(raw, pageUrl); } catch (e) { continue; }
        if (!/^https?:$/.test(u.protocol)) continue;
        const sameOrigin = u.origin === self.location.origin;
        if (sameOrigin && NEVER_RE.test(u.pathname)) continue;
        if (await cache.match(u.href)) continue;               // already saved

        try {
            const res = sameOrigin
                ? await fetch(u.href)
                : await fetch(u.href, { mode: 'cors', credentials: 'omit' });
            const small = Number(res.headers.get('content-length') || 0) < 2 * 1024 * 1024;
            if (res.ok && small && (res.type === 'basic' || res.type === 'cors')) {
                await cache.put(u.href, res.clone());
                fetched++;
            }
        } catch (e) { /* one missing file must not stop the rest */ }
    }
}

self.addEventListener('periodicsync', (event) => {
    if (event.tag === 'smasa-refresh') event.waitUntil(enqueue(() => precache({ force: false })));
});

/* ------------------------------- status shared with the pages ------------------------------- */
async function buildStatus(extra) {
    const manifest = await getManifest();
    const cache = await caches.open(PAGES);
    const pages = [];

    for (const p of manifest.pages) {
        const hit = await cache.match(pageKey(p.path));
        pages.push({
            key: p.key, label: p.label, desc: p.desc, icon: p.icon, path: p.path,
            savedAt: hit ? Number(hit.headers.get('X-SW-Cached-At') || 0) || null : null,
        });
    }

    return Object.assign({
        type: 'status',
        pages,
        total: pages.length,
        ready: pages.filter((p) => p.savedAt).length,
        syncedAt: Number(await getSetting('syncedAt')) || 0,
        refreshHours: Math.round((manifest.refreshMs || DEFAULT_REFRESH_MS) / 3600000),
        syncing: false,
    }, extra || {});
}

async function broadcast(status) {
    const s = status || await buildStatus();
    const all = await self.clients.matchAll({ includeUncontrolled: true });
    all.forEach((c) => c.postMessage(s));
}

/* --------------------------------- the "Offline pages" screen --------------------------------- */
function offlineIndexPage(missingUrl, status) {
    const initial = JSON.stringify({ status, missing: !!missingUrl }).replace(/</g, '\\u003c');

    const html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
        + '<meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline pages</title><style>'
        + '*{box-sizing:border-box}body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#f1f5f9;margin:0;padding:20px;color:#0f172a}'
        + '.box{max-width:680px;margin:0 auto;background:#fff;border-radius:16px;padding:22px 24px;box-shadow:0 4px 24px rgba(0,0,0,.08)}'
        + '.top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}'
        + 'h1{margin:0 0 4px;font-size:21px}.sub{color:#64748b;font-size:13px;margin:0}'
        + '.net{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;padding:5px 11px;border-radius:99px}'
        + '.net i{width:8px;height:8px;border-radius:50%;display:inline-block}'
        + '.on{background:#dcfce7;color:#166534}.on i{background:#16a34a}.off{background:#fef3c7;color:#92400e}.off i{background:#d97706}'
        + '.warn{background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;border-radius:10px;padding:10px 12px;font-size:13px;margin:14px 0 0}'
        + '.bar{height:8px;background:#e2e8f0;border-radius:99px;overflow:hidden;margin:16px 0 6px}.bar b{display:block;height:100%;background:#2f2ccb;width:0;transition:width .3s}'
        + '.sum{display:flex;justify-content:space-between;gap:10px;font-size:12.5px;color:#475569;flex-wrap:wrap}'
        + 'ul{list-style:none;padding:0;margin:14px 0 0}'
        + 'li{display:flex;align-items:center;gap:12px;padding:12px 0;border-top:1px solid #e2e8f0}'
        + '.ic{font-size:22px;width:38px;height:38px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0}'
        + '.nm{flex:1;min-width:0}.nm a,.nm span.t{font-weight:700;font-size:14.5px;color:#2f2ccb;text-decoration:none}.nm span.t{color:#94a3b8}'
        + '.nm small{display:block;color:#64748b;font-size:12px;margin-top:2px}'
        + '.st{font-size:11.5px;font-weight:700;padding:4px 10px;border-radius:99px;white-space:nowrap}'
        + '.ok{background:#dcfce7;color:#166534}.no{background:#fef3c7;color:#92400e}.wk{background:#e0e7ff;color:#3730a3}'
        + 'button{font:700 13px system-ui;background:#2f2ccb;color:#fff;border:0;border-radius:9px;padding:9px 14px;cursor:pointer}button:disabled{background:#cbd5e1;cursor:not-allowed}'
        + '.foot{margin-top:16px;color:#64748b;font-size:12px;line-height:1.5}'
        + '</style></head><body><div class="box">'
        + '<div class="top"><div><h1>Offline pages</h1><p class="sub" id="sub"></p></div><span class="net" id="net"></span></div>'
        + '<div class="warn" id="warn" style="display:none"></div>'
        + '<div class="bar"><b id="fill"></b></div>'
        + '<div class="sum"><span id="count"></span><span id="synced"></span></div>'
        + '<ul id="list"></ul>'
        + '<div style="margin-top:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap"><button id="upd">Update now</button><span class="sub" id="store"></span></div>'
        + '<div class="foot">These pages are kept on this device and refreshed automatically about every <span id="hrs">6</span> hours while you are online. '
        + 'Changes cannot be saved while offline. Pages are removed from this device when you sign out.</div>'
        + '</div><script>(function(){'
        + 'var INIT=' + initial + ',S=INIT.status,MISSING=INIT.missing;'
        + 'function $(i){return document.getElementById(i)}'
        + 'function ago(t){if(!t)return"";var m=Math.round((Date.now()-t)/60000);if(m<1)return"just now";if(m<60)return m+" min ago";'
        + 'var h=Math.round(m/60);if(h<24)return h+" h ago";return Math.round(h/24)+" d ago"}'
        + 'function el(tag,cls,txt){var e=document.createElement(tag);if(cls)e.className=cls;if(txt!=null)e.textContent=txt;return e}'
        + 'function render(){var on=navigator.onLine;var n=$("net");n.className="net "+(on?"on":"off");n.textContent="";n.appendChild(el("i"));n.appendChild(document.createTextNode(on?"Online":"Offline"));'
        + '$("sub").textContent=on?"Pages saved on this device for when the internet is down.":"You are offline. These pages still open.";'
        + 'var w=$("warn");if(MISSING){w.style.display="block";w.textContent=on?"That page is not available offline.":"You are offline and that page is not available offline. Pages that work offline are listed below."}else w.style.display="none";'
        + 'var total=S.total||0,ready=S.ready||0;$("fill").style.width=(total?Math.round(ready*100/total):0)+"%";'
        + '$("count").textContent=total?(S.syncing?"Saving pages... "+(S.done||0)+" of "+(S.due||S.total):ready+" of "+total+" pages ready"):"No offline pages for this account.";'
        + '$("synced").textContent=S.syncedAt?"Last updated "+ago(S.syncedAt):"";$("hrs").textContent=S.refreshHours||6;'
        + 'var ul=$("list");ul.textContent="";'
        + '(S.pages||[]).forEach(function(p){var li=el("li");li.appendChild(el("div","ic",p.icon||"📄"));'
        + 'var nm=el("div","nm");var usable=!!p.savedAt||on;'
        + 'if(usable){var a=el("a",null,p.label);a.href=p.path;nm.appendChild(a)}else nm.appendChild(el("span","t",p.label));'
        + 'if(p.desc)nm.appendChild(el("small",null,p.desc));li.appendChild(nm);'
        + 'li.appendChild(p.savedAt?el("span","st ok","Ready - saved "+ago(p.savedAt)):el("span","st no",on?"Not saved yet":"Not saved on this device"));ul.appendChild(li)});'
        + '$("upd").disabled=!on||S.syncing||!total;$("upd").textContent=S.syncing?"Saving...":"Update now"}'
        + 'navigator.serviceWorker.addEventListener("message",function(e){var d=e.data;if(d&&d.type==="status"){S=d;render()}});'
        + 'function ask(){var c=navigator.serviceWorker.controller;if(c)c.postMessage({type:"status"})}'
        + 'window.addEventListener("online",function(){render();ask()});window.addEventListener("offline",render);'
        + '$("upd").onclick=function(){var c=navigator.serviceWorker.controller;if(c){S.syncing=true;S.done=0;render();c.postMessage({type:"precache",force:true})}};'
        + 'if(navigator.storage&&navigator.storage.estimate)navigator.storage.estimate().then(function(e){if(e&&e.usage!=null)$("store").textContent="Using "+(e.usage/1048576).toFixed(1)+" MB on this device"}).catch(function(){});'
        + 'render();ask();setInterval(render,30000);'
        + '})();</script></body></html>';

    return new Response(html, {
        status: missingUrl ? 503 : 200,
        headers: { 'Content-Type': 'text/html; charset=UTF-8' },
    });
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
