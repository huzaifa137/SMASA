/*!
 * SMASA UX engine
 * ---------------------------------------------------------------------------
 * Replaces "success alert -> OK -> location.reload()" with:
 *
 *   SMASA.done('Saved', 'Category added.')   toast + in-place table refresh
 *   SMASA.refresh()                          in-place refresh only
 *   SMASA.toast('error', 'Oops', 'details')  toast only
 *   SMASA.reload('Term switched')            real reload (toast survives it)
 *
 * How the in-place refresh works
 *   1. Re-fetches the current URL (same page number / filters).
 *   2. Swaps only the "zones": elements marked data-smasa-zone, otherwise
 *      every <table> wrapper or its card (outside modals/forms). Counters
 *      marked .stat-chip / data-smasa-live are refreshed too. Forms, modals and their
 *      event handlers are never touched.
 *   3. New or changed rows glow briefly (.row-flash).
 *   4. If anything looks unsafe (login redirect, DataTable, zone mismatch,
 *      network error) it falls back to a normal reload, and the toast is
 *      stashed in sessionStorage so the user still sees it after the reload.
 *
 * Page mode (whole page body, via vendored morphdom)
 *   SMASA.donePage(title, text) / SMASA.refreshPage()
 *   Keeps scroll, open modals, active tabs, select2, focused/typed fields outside tables.
 *   SMASA.bind('key', fn) re-runs fn after each refresh (rebinds row buttons).
 *
 * Opt-in markup
 *   data-smasa-zone            force a container to be a refresh zone
 *   data-smasa-ignore          exclude a table/container from refreshing
 *   data-smasa-keep            POST form that must NOT be reset after SMASA.done()
 *   <form data-smasa-ajax>     submit via fetch, toast the Laravel flash
 *                              message, refresh zones (no page reload)
 *
 * Laravel flash messages (session('success') etc.) are bridged to toasts by
 * layouts/partials/smasa-flash.blade.php.
 */
(function (window, document) {
    'use strict';
    if (window.SMASA) { return; }

    var BASE = (document.currentScript && document.currentScript.src || '').replace(/[^\/]*(\?.*)?$/, '');
    var STASH = 'smasa:toast';
    var inFlight = null;

    /* ------------------------------------------------------------------ toast */
    function swalToast() {
        if (!window.Swal) { return null; }
        return window.Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3200,
            timerProgressBar: true,
            didOpen: function (t) {
                t.addEventListener('mouseenter', window.Swal.stopTimer);
                t.addEventListener('mouseleave', window.Swal.resumeTimer);
            }
        });
    }

    function fallbackToast(icon, title, text) {
        var box = document.getElementById('smasa-toast-box');
        if (!box) {
            box = document.createElement('div');
            box.id = 'smasa-toast-box';
            document.body.appendChild(box);
        }
        var el = document.createElement('div');
        el.className = 'smasa-toast smasa-toast-' + icon;
        el.setAttribute('role', 'status');
        var b = document.createElement('strong');
        b.textContent = title || '';
        el.appendChild(b);
        if (text) {
            var s = document.createElement('span');
            s.textContent = text;
            el.appendChild(s);
        }
        box.appendChild(el);
        setTimeout(function () { el.classList.add('out'); }, 3000);
        setTimeout(function () { if (el.parentNode) { el.parentNode.removeChild(el); } }, 3400);
    }

    function toast(icon, title, text) {
        icon = icon || 'success';
        var t = swalToast();
        if (t) {
            var o = { icon: icon, title: title || '' };
            if (text) { o.text = text; }
            t.fire(o);
        } else {
            fallbackToast(icon, title, text);
        }
    }

    function stash(icon, title, text) {
        try { sessionStorage.setItem(STASH, JSON.stringify({ i: icon, t: title, x: text })); } catch (e) { /* ignore */ }
    }

    function showStashed() {
        try {
            var raw = sessionStorage.getItem(STASH);
            if (!raw) { return; }
            sessionStorage.removeItem(STASH);
            var d = JSON.parse(raw);
            toast(d.i, d.t, d.x);
        } catch (e) { /* ignore */ }
    }

    /* ---------------------------------------------------------- zone helpers */
    function isDataTable(table) {
        var $ = window.jQuery;
        return !!($ && $.fn && $.fn.dataTable && $.fn.dataTable.isDataTable(table));
    }

    function zonesOf(root) {
        var explicit = root.querySelectorAll('[data-smasa-zone]');
        if (explicit.length) { return Array.prototype.slice.call(explicit); }
        var out = [];
        Array.prototype.forEach.call(root.querySelectorAll('table'), function (t) {
            if (t.closest('.modal, .modal-overlay, form, [data-smasa-ignore], thead')) { return; }
            var z = t.closest('.table-responsive') || t.parentElement;
            // Prefer the whole card (so "12 total" headers refresh too) unless it holds its own controls.
            var card = t.closest('.lib-card, .card');
            if (card) {
                var own = Array.prototype.some.call(
                    card.querySelectorAll('form, input, select, textarea, [type="submit"]'),
                    function (el) { return !el.closest('table'); });
                if (!own) { z = card; }
            }
            if (z && out.indexOf(z) === -1) { out.push(z); }
        });
        return out;
    }

    function rowSet(zone) {
        var s = {};
        Array.prototype.forEach.call(zone.querySelectorAll('tbody tr'), function (r) {
            s[r.textContent.replace(/\s+/g, ' ').trim()] = 1;
        });
        return s;
    }

    function swap(liveDoc, newDoc) {
        var live = zonesOf(liveDoc);
        var next = zonesOf(newDoc);
        if (live.length !== next.length) { return false; }
        if (!live.length && !liveTables().length) { return false; }
        live = live.filter(function (z, i) { var t = z.querySelector('table'); if (t && isDataTable(t)) { next[i] = null; return false; } return true; });
        next = next.filter(Boolean);
        refreshDataTables(newDoc);
        live.forEach(function (zone, i) {
            var before = rowSet(zone);
            var s2 = [];
            Array.prototype.forEach.call(zone.querySelectorAll('select'), function (sel, k) {
                if (sel.classList.contains('select2-hidden-accessible')) { s2.push(k); }
            });
            var fresh = document.importNode(next[i], true);
            zone.parentNode.replaceChild(fresh, zone);
            if (s2.length && window.jQuery && window.jQuery.fn.select2) {
                var sels = fresh.querySelectorAll('select');
                s2.forEach(function (k) { if (sels[k]) { window.jQuery(sels[k]).select2(); } });
            }
            Array.prototype.forEach.call(fresh.querySelectorAll('tbody tr'), function (r) {
                if (!before[r.textContent.replace(/\s+/g, ' ').trim()]) { r.classList.add('row-flash'); }
            });
        });
        try {
            if (window.jQuery && window.jQuery.fn.tooltip) { window.jQuery('[data-toggle="tooltip"]').tooltip(); }
        } catch (e) { /* ignore */ }
        // Live counters (e.g. "12 total records") are plain display text: refresh by position.
        var LIVE = '.stat-chip, [data-smasa-live]';
        var la = document.querySelectorAll(LIVE), lb = newDoc.querySelectorAll(LIVE);
        if (la.length === lb.length) {
            Array.prototype.forEach.call(la, function (el, k) { el.innerHTML = lb[k].innerHTML; });
        }
        document.dispatchEvent(new CustomEvent('smasa:refreshed'));
        return true;
    }


    /* ------------------------------------------------------------ DataTables */
    function liveTables() {
        var $ = window.jQuery, out = [];
        if (!($ && $.fn && $.fn.dataTable)) { return out; }
        Array.prototype.forEach.call(document.querySelectorAll('table'), function (t) { if (isDataTable(t)) { out.push(t); } });
        return out;
    }

    // Re-load DataTables without losing the current page, search or sort.
    function refreshDataTables(newDoc) {
        var $ = window.jQuery;
        liveTables().forEach(function (t, k) {
            try {
                var dt = $(t).DataTable();
                var st = dt.settings()[0];
                if (st.oFeatures.bServerSide || st.ajax) { dt.ajax.reload(null, false); return; }
                var twin = (t.id && newDoc.getElementById(t.id)) || newDoc.querySelectorAll('table')[k];
                if (!twin) { return; }
                var rows = twin.querySelectorAll('tbody tr');
                dt.clear();
                dt.rows.add($(Array.prototype.map.call(rows, function (r) { return document.importNode(r, true); }))).draw(false);
            } catch (e) { /* leave table as is */ }
        });
    }

    /* ------------------------------------------------------- page-wide morph */
    var morphPromise = null;
    function loadMorph() {
        if (window.morphdom) { return Promise.resolve(window.morphdom); }
        if (!morphPromise) {
            morphPromise = new Promise(function (ok, bad) {
                var sc = document.createElement('script');
                sc.src = BASE + 'vendors/morphdom-umd.min.js';
                sc.onload = function () { ok(window.morphdom); };
                sc.onerror = bad;
                document.head.appendChild(sc);
            });
        }
        return morphPromise;
    }

    var SKIP = '[data-smasa-ignore], .modal, .modal-overlay, .nt-modal-overlay, .modal-backdrop, .swal2-container, ' +
               '.select2-container, .dataTables_wrapper, header, .app-header, .app-sidebar, .horizontalMenu, #global-loader';
    var KEEP_STATE = '.tab-pane, .nav-link, .nav-item, .collapse, .collapsing, .accordion-collapse, .dropdown, .dropdown-menu, [data-smasa-keepstate], .scheme-list-item, .scale-list-item, .as-detail';

    function rootOf(doc) {
        return doc.querySelector('[data-smasa-root]') || doc.querySelector('.side-app') ||
               doc.querySelector('.app-content .container') || doc.querySelector('.app-content');
    }

    function morphPage(newDoc) {
        var from = rootOf(document), to = rootOf(newDoc);
        if (!from || !to) { return Promise.resolve(false); }
        return loadMorph().then(function (morphdom) {
            var focused = document.activeElement;
            morphdom(from, to, {
                childrenOnly: true,
                getNodeKey: function (n) { return n.nodeType === 1 ? (n.id || n.getAttribute('data-key') || null) : null; },
                onBeforeNodeAdded: function (n) { return n.nodeType === 1 && n.tagName === 'SCRIPT' ? false : n; },
                onNodeAdded: function (n) {
                    if (n.nodeType === 1 && n.tagName === 'TR' && n.closest('tbody')) { n.classList.add('row-flash'); }
                    return n;
                },
                onBeforeElUpdated: function (a, b) {
                    if (a.isEqualNode(b)) { return false; }
                    if (a.matches && a.matches(SKIP)) { return false; }
                    if (a.tagName === 'CANVAS' || a.querySelector(':scope > canvas, :scope > .apexcharts-canvas, :scope > .highcharts-container')) { return false; }
                    if (a.matches(KEEP_STATE)) {
                        b.className = a.className;
                        if (a.hasAttribute('style')) { b.setAttribute('style', a.getAttribute('style')); } else { b.removeAttribute('style'); }
                        ['aria-expanded', 'aria-selected', 'aria-hidden'].forEach(function (k) {
                            if (a.hasAttribute(k)) { b.setAttribute(k, a.getAttribute(k)); }
                        });
                    }
                    // Keep what the user is typing outside tables; table cells always show server truth.
                    if (/^(INPUT|TEXTAREA|SELECT)$/.test(a.tagName) && !a.closest('tbody')) {
                        if (a.type !== 'hidden') { return false; }
                    }
                    if (a === focused) { return false; }
                    return true;
                },
                onBeforeElChildrenUpdated: function (a) { return !(a.matches && a.matches(SKIP)); }
            });
            refreshDataTables(newDoc);
            var LIVE = '.stat-chip, [data-smasa-live]';
            var la = document.querySelectorAll(LIVE), lb = newDoc.querySelectorAll(LIVE);
            if (la.length === lb.length) { Array.prototype.forEach.call(la, function (el, k) { el.innerHTML = lb[k].innerHTML; }); }
            // master/detail lists: if the selected item vanished (deleted), select the first one again
            ['.scheme-list-item', '.scale-list-item'].forEach(function (sel) {
                var items = document.querySelectorAll(sel);
                if (items.length && !document.querySelector(sel + '.active')) { items[0].click(); }
            });
            rebind();
            document.dispatchEvent(new CustomEvent('smasa:refreshed'));
            return true;
        });
    }

    /* --------------------------------------------- rebind hooks for new nodes */
    var binders = {};
    // SMASA.bind('key', fn): runs fn now and again after every in-place refresh.
    // Use for direct addEventListener loops over rows (guard against double binding inside fn).
    function bind(key, fn) { binders[key] = fn; fn(); }
    function rebind() {
        Object.keys(binders).forEach(function (k) { try { binders[k](); } catch (e) { /* ignore */ } });
    }

    /* ---------------------------------------------------- sidebar badges */
    // The sidebar is never morphed (it is in SKIP), so its counters (Marks Entry,
    // Create Assessment, Examinations total) are copied over by hand from the
    // freshly fetched page. Items are always in the DOM and just hidden at 0.
    function syncBadges(newDoc) {
        try {
            Array.prototype.forEach.call(document.querySelectorAll('[data-smasa-badge]'), function (el) {
                var key = el.getAttribute('data-smasa-badge');
                var fresh = newDoc.querySelector('[data-smasa-badge="' + key + '"]');
                if (!fresh) { return; }
                var n = parseInt((fresh.textContent || '0').trim(), 10) || 0;
                el.textContent = String(n);
                el.classList.toggle('smasa-hidden', n <= 0);
            });
            Array.prototype.forEach.call(document.querySelectorAll('[data-smasa-badge-item]'), function (li) {
                var key = li.getAttribute('data-smasa-badge-item');
                var fresh = newDoc.querySelector('[data-smasa-badge-item="' + key + '"]');
                if (fresh) { li.classList.toggle('smasa-hidden', fresh.classList.contains('smasa-hidden')); }
            });
        } catch (e) { /* never block a refresh because of a badge */ }
    }

    /* --------------------------------------------------------------- refresh */
    function refresh(opts) {
        opts = opts || {};
        if (inFlight) { return inFlight; }
        var page = opts.mode === 'page';
        if (!page && !zonesOf(document).length && !liveTables().length) {
            if (opts.toast) { stash(opts.toast[0], opts.toast[1], opts.toast[2]); }
            window.location.reload();
            return Promise.resolve(false);
        }
        inFlight = fetch(opts.url || window.location.href, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-SMASA-Soft': '1' }
        }).then(function (res) {
            if (!res.ok || /\/login|\/logout/i.test(res.url)) { throw new Error('unsafe'); }
            return res.text();
        }).then(function (html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            syncBadges(doc);
            if (page) {
                return morphPage(doc).then(function (ok) { if (!ok) { throw new Error('no-root'); } return true; });
            }
            if (!swap(document, doc)) { throw new Error('no-zone'); }
            return true;
        }).catch(function () {
            if (opts.toast) { stash(opts.toast[0], opts.toast[1], opts.toast[2]); }
            window.location.reload();
            return false;
        }).then(function (ok) {
            inFlight = null;
            return ok;
        });
        return inFlight;
    }

    // After a successful save the page used to reload, which closed modals and
    // emptied add-forms. Do the same by hand, since we no longer reload.
    function settle() {
        try {
            Array.prototype.forEach.call(document.querySelectorAll('.modal-overlay.active'), function (m) { m.classList.remove('active'); });
            if (window.jQuery && window.jQuery.fn.modal) { window.jQuery('.modal.show').modal('hide'); }
            Array.prototype.forEach.call(document.forms, function (f) {
                if ((f.getAttribute('method') || '').toLowerCase() === 'post' && !f.hasAttribute('data-smasa-keep') && !f.closest('table')) { f.reset(); }
            });
        } catch (e) { /* ignore */ }
    }

    function done(title, text, opts) {
        opts = opts || {};
        var icon = opts.icon || 'success';
        settle();
        toast(icon, title, text);
        return refresh({ toast: [icon, title, text], url: opts.url, mode: opts.mode });
    }

    function hardReload(title, text) {
        if (title) { stash('success', title, text); }
        window.location.reload();
    }

    /* ------------------------------------------------------------ flash/JSON */
    function showFlash(root) {
        var el = (root || document).getElementById('smasa-flash');
        if (!el) { return; }
        try {
            var list = JSON.parse(el.textContent || '[]');
            list.forEach(function (m) { toast(m.type, m.message); });
        } catch (e) { /* ignore */ }
        el.parentNode.removeChild(el);
    }

    /* ------------------------------------------------------------ ajax forms */
    function firstError(json) {
        if (!json) { return 'Something went wrong.'; }
        if (json.errors) {
            var k = Object.keys(json.errors)[0];
            if (k) { return [].concat(json.errors[k])[0]; }
        }
        return json.message || 'Something went wrong.';
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.hasAttribute || !form.hasAttribute('data-smasa-ajax') || e.defaultPrevented) { return; }
        e.preventDefault();
        var btn = form.querySelector('[type="submit"]');
        var label = btn ? btn.innerHTML : null;
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; }
        var restore = function () { if (btn) { btn.disabled = false; btn.innerHTML = label; } };

        fetch(form.action, {
            method: (form.method || 'POST').toUpperCase(),
            body: new FormData(form),
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html,application/json' }
        }).then(function (res) {
            var json = /json/.test(res.headers.get('content-type') || '');
            if (!res.ok) {
                return (json ? res.json() : Promise.resolve(null)).then(function (j) { throw new Error(firstError(j)); });
            }
            return json ? res.json().then(function (j) { return { json: j }; })
                        : res.text().then(function (h) { return { html: h }; });
        }).then(function (r) {
            restore();
            if (r.json) {
                if (r.json.success === false) { throw new Error(firstError(r.json)); }
                toast('success', r.json.message || 'Saved');
                if (form.hasAttribute('data-smasa-reset')) { form.reset(); }
                return refresh();
            }
            var doc = new DOMParser().parseFromString(r.html, 'text/html');
            syncBadges(doc);
            var flash = doc.getElementById('smasa-flash');
            if (flash) { showFlash(doc); } else { toast('success', 'Saved'); }
            if (form.hasAttribute('data-smasa-reset')) { form.reset(); }
            if (!swap(document, doc)) { return refresh(); }
        }).catch(function (err) {
            restore();
            toast('error', 'Could not save', err.message);
        });
    });

    /* ------------------------------------------------------------------ boot */
    function boot() { showStashed(); showFlash(document); }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }

    window.SMASA = {
        settle: settle, syncBadges: syncBadges, toast: toast, refresh: refresh, done: done, reload: hardReload, bind: bind,
        // page mode: patches the whole page body in place (stats, badges, steppers, tables, DataTables)
        refreshPage: function () { return refresh({ mode: 'page' }); },
        donePage: function (title, text, o) { o = o || {}; o.mode = 'page'; return done(title, text, o); }
    };
})(window, document);
