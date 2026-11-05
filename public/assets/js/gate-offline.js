/*!
 * SMASA offline gate scanning.
 *
 * What it does, in plain words:
 *  - While the gate device is online it keeps a small list of the school's
 *    active cards (name + class only) in the browser (IndexedDB).
 *  - If the internet drops, arrival / sign-out scans are saved on the device
 *    with their real time and a unique id, and the screen says "saved offline".
 *  - When the connection is back (or the operator taps the status pill) the
 *    saved scans are sent to the server, oldest first. The server decides
 *    late / on-time from the ORIGINAL scan time and ignores repeats, so it is
 *    safe if the connection drops in the middle of a send.
 *
 * Only categories listed in config.categories (arrival, sign-out) are handled
 * offline: fees, library and card info need live server data.
 */
(function (global) {
    'use strict';

    var DB_NAME = 'smasa-gate', DB_VERSION = 1;
    var cfg = null, db = null;
    var online = navigator.onLine, authProblem = false, syncing = false;
    var csrf = null, offsetMs = 0;            // offsetMs = server clock - device clock
    var pillEl = null, pendingCount = 0, lastRejected = [];
    var GateOffline = { onSynced: null };

    // ---------- IndexedDB helpers ----------
    function openDb() {
        return new Promise(function (resolve, reject) {
            if (!global.indexedDB) { reject(new Error('IndexedDB not supported')); return; }
            var req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = function (e) {
                var d = e.target.result;
                d.createObjectStore('cards', { keyPath: 'card_number' });
                d.createObjectStore('queue', { keyPath: 'client_id' });
                d.createObjectStore('meta', { keyPath: 'k' });
            };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error); };
        });
    }

    function store(name, mode) { return db.transaction(name, mode).objectStore(name); }
    function wrap(req) {
        return new Promise(function (resolve, reject) {
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error); };
        });
    }
    function getAll(name) { return wrap(store(name, 'readonly').getAll()); }
    function put(name, value) { return wrap(store(name, 'readwrite').put(value)); }
    function del(name, key) { return wrap(store(name, 'readwrite').delete(key)); }
    function getMeta(k) { return wrap(store('meta', 'readonly').get(k)).then(function (r) { return r ? r.v : null; }); }
    function setMeta(k, v) { return put('meta', { k: k, v: v }); }

    function uuid() {
        if (global.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'x' + Date.now().toString(36) + Math.random().toString(36).slice(2, 12);
    }

    function serverNow() { return Date.now() + offsetMs; }

    function parseCard(raw) {
        raw = String(raw || '').trim();
        try { var j = JSON.parse(raw); if (j && j.card) return String(j.card); } catch (e) { /* plain number */ }
        return raw;
    }

    function fmtTime(ms) {
        return new Date(ms).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    function dayKey(ms) {
        var d = new Date(ms);
        return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
    }

    // ---------- Network ----------
    function timeoutFetch(url, opts, ms) {
        var ctl = ('AbortController' in global) ? new AbortController() : null;
        var timer = setTimeout(function () { if (ctl) ctl.abort(); }, ms || 5000);
        opts = opts || {};
        if (ctl) opts.signal = ctl.signal;
        return fetch(url, opts).then(function (r) { clearTimeout(timer); return r; },
            function (e) { clearTimeout(timer); throw e; });
    }

    /** Are we really online (and still logged in)? navigator.onLine alone can't tell. */
    function ping() {
        var t0 = Date.now();
        return timeoutFetch(cfg.pingUrl, { cache: 'no-store', credentials: 'same-origin', headers: { 'Accept': 'application/json' } }, 4000)
            .then(function (r) {
                var json = (r.headers.get('content-type') || '').indexOf('json') !== -1;
                if (r.redirected || r.status === 401 || r.status === 419 || (r.ok && !json)) {
                    online = true; authProblem = true; return;          // reachable, but logged out
                }
                if (!r.ok) { online = false; return; }
                return r.json().then(function (d) {
                    online = true; authProblem = false;
                    csrf = d.csrf;
                    offsetMs = d.time_ms - (t0 + Date.now()) / 2;       // corrects a wrong device clock
                });
            })
            .catch(function () { online = false; })
            .then(function () { return updatePill(); });
    }

    function refreshCards() {
        if (!online || authProblem) return Promise.resolve(0);
        return timeoutFetch(cfg.cardsUrl, { cache: 'no-store', credentials: 'same-origin', headers: { 'Accept': 'application/json' } }, 20000)
            .then(function (r) { if (!r.ok) throw new Error('cards ' + r.status); return r.json(); })
            .then(function (d) {
                return getMeta('school_id').then(function (sid) {
                    // A different school's list must never be mixed in.
                    var wipe = sid && sid !== d.school_id ? wrap(store('cards', 'readwrite').clear()) : Promise.resolve();
                    return wipe.then(function () {
                        var tx = db.transaction('cards', 'readwrite'), s = tx.objectStore('cards');
                        s.clear();
                        d.cards.forEach(function (c) { s.put(c); });
                        return new Promise(function (res, rej) { tx.oncomplete = res; tx.onerror = function () { rej(tx.error); }; });
                    });
                }).then(function () { return setMeta('school_id', d.school_id); })
                  .then(function () { return setMeta('cards_at', Date.now()); })
                  .then(function () { return d.cards.length; });
            })
            .catch(function () { return 0; });
    }

    // ---------- Queue ----------
    function queueForSchool() {
        return getAll('queue').then(function (all) {
            return all.filter(function (q) { return q.school_id === cfg.schoolId; })
                      .sort(function (a, b) { return a.at - b.at; });
        });
    }

    function refreshCount() {
        return queueForSchool().then(function (q) { pendingCount = q.length; updatePill(); return q.length; });
    }

    /** Save one scan on this device. Resolves with what to show the operator. */
    GateOffline.enqueue = function (rawCard, category) {
        var card = parseCard(rawCard);
        if (!card) return Promise.resolve({ ok: false, message: 'No card number.' });

        var at = Math.round(serverNow());

        return wrap(store('cards', 'readonly').get(card)).then(function (known) {
            return queueForSchool().then(function (q) {
                var same = q.filter(function (x) {
                    return x.card_number === card && x.category === category && dayKey(x.at) === dayKey(at);
                })[0];
                var label = category === 'attendance_departure' ? 'sign-out' : 'arrival';

                if (same) {
                    return {
                        ok: true, duplicate: true, name: same.name, type: same.type, klass: same.klass,
                        message: (same.name || 'This card') + ' already has a ' + label + ' saved offline at ' + fmtTime(same.at) + '.'
                    };
                }

                var item = {
                    client_id: uuid(), school_id: cfg.schoolId, card_number: card, category: category,
                    at: at, name: known ? known.name : null, type: known ? known.type : null,
                    klass: known ? known.class : null
                };

                return put('queue', item).then(function () { return refreshCount(); }).then(function () {
                    return {
                        ok: true, known: !!known, name: item.name, type: item.type, klass: item.klass,
                        label: label, time: fmtTime(at),
                        message: (item.name || 'Card ' + card) + ' - ' + label + ' saved offline at ' + fmtTime(at)
                            + (known ? '' : ' (card not in the offline list; it will be checked when online)')
                    };
                });
            });
        });
    };

    GateOffline.eligible = function (category) {
        return cfg && cfg.categories.indexOf(category) !== -1 && !!db;
    };
    GateOffline.isOffline = function () { return !online; };
    GateOffline.pending = function () { return pendingCount; };

    /** Send the saved scans, oldest first, 50 at a time. */
    GateOffline.sync = function () {
        if (syncing || !online || authProblem || !csrf || !db) return Promise.resolve();
        syncing = true; updatePill();

        var summary = { synced: 0, duplicates: 0, rejected: [] };

        function sendBatch() {
            return queueForSchool().then(function (q) {
                if (!q.length) return;
                var batch = q.slice(0, 50), byId = {};
                batch.forEach(function (b) { byId[b.client_id] = b; });

                return timeoutFetch(cfg.syncUrl, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ scans: batch.map(function (b) {
                        return { client_id: b.client_id, card_number: b.card_number, category: b.category, scanned_at_ms: b.at };
                    }) })
                }, 60000).then(function (r) {
                    var json = (r.headers.get('content-type') || '').indexOf('json') !== -1;
                    if (r.redirected || r.status === 401 || r.status === 419 || !json) { authProblem = true; throw new Error('auth'); }
                    if (!r.ok) throw new Error('server ' + r.status);
                    return r.json();
                }).then(function (d) {
                    var chain = Promise.resolve(), keepGoing = false;
                    d.results.forEach(function (res) {
                        var item = byId[res.client_id];
                        if (res.status === 'retry') return;               // stays in the queue
                        chain = chain.then(function () { return del('queue', res.client_id); });
                        if (res.status === 'synced') summary.synced++;
                        else if (res.status === 'duplicate') summary.duplicates++;
                        else summary.rejected.push({
                            name: (item && item.name) || (item && item.card_number) || 'Card',
                            time: item ? fmtTime(item.at) : '', message: res.message
                        });
                        keepGoing = true;
                    });
                    return chain.then(function () { return keepGoing ? sendBatch() : undefined; });
                });
            });
        }

        return sendBatch().catch(function () { /* stays queued; the pill shows why */ })
            .then(function () { syncing = false; return refreshCount(); })
            .then(function () {
                if ((summary.synced || summary.duplicates || summary.rejected.length) && typeof GateOffline.onSynced === 'function') {
                    GateOffline.onSynced(summary);
                }
            });
    };

    // ---------- Status pill ----------
    function ensurePill() {
        if (pillEl) return pillEl;
        pillEl = document.createElement('button');
        pillEl.type = 'button';
        pillEl.id = 'gateNetPill';
        pillEl.style.cssText = 'position:fixed;left:14px;bottom:14px;z-index:99999;border:0;border-radius:99px;'
            + 'padding:.5rem .95rem;font:600 .8rem/1 system-ui,-apple-system,Segoe UI,sans-serif;color:#fff;'
            + 'box-shadow:0 4px 14px rgba(0,0,0,.25);cursor:pointer;display:flex;gap:.45rem;align-items:center';
        pillEl.addEventListener('click', function () {
            if (authProblem) { window.location.href = cfg.loginUrl || window.location.href; return; }
            ping().then(function () { return GateOffline.sync(); });
        });
        document.body.appendChild(pillEl);
        return pillEl;
    }

    function updatePill() {
        var el = ensurePill(), bg, text;
        if (authProblem) { bg = '#dc2626'; text = pendingCount + ' scan(s) waiting - tap to sign in again'; }
        else if (!online) { bg = '#d97706'; text = 'Offline - ' + pendingCount + ' scan(s) saved'; }
        else if (syncing) { bg = '#2563eb'; text = 'Syncing ' + pendingCount + ' scan(s)...'; }
        else if (pendingCount > 0) { bg = '#2563eb'; text = pendingCount + ' scan(s) waiting - tap to sync'; }
        else { bg = '#059669'; text = 'Online'; }
        el.style.background = bg;
        el.textContent = text;
        el.style.opacity = (online && !pendingCount && !authProblem) ? '.75' : '1';
    }

    // ---------- Start-up ----------
    GateOffline.init = function (config) {
        cfg = config;

        // The page itself is kept for offline use by the site worker (/sw.js, see partials/offline-support).

        return openDb().then(function (d) {
            db = d;
            return refreshCount();
        }).then(function () {
            global.addEventListener('online', function () { ping().then(function () { return GateOffline.sync(); }); });
            global.addEventListener('offline', function () { online = false; updatePill(); });

            // Heartbeat: notice drops and returns that the browser events miss.
            setInterval(function () {
                var was = online;
                ping().then(function () { if (online && !was) GateOffline.sync(); });
            }, 20000);
            // Keep the offline card list fresh.
            setInterval(refreshCards, 30 * 60 * 1000);

            return ping();
        }).then(function () {
            return refreshCards();
        }).then(function () {
            return GateOffline.sync();
        }).catch(function () { db = null; updatePill(); });
    };

    global.GateOffline = GateOffline;
})(window);
