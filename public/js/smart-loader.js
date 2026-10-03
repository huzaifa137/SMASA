/*!
 * SMASA Smart Loader
 * ---------------------------------------------------------------------------
 * Instant visual feedback for anything the user triggers, without reloading
 * the page and without polling or per-frame work:
 *
 *   - a slim progress bar at the top of the screen,
 *   - a spinner on the control that was clicked (and the control ignores
 *     further clicks until the action finishes, so no double submits),
 *   - a soft "Please wait" overlay for page navigations, form posts and
 *     file downloads that take longer than ~350ms.
 *
 * What it watches (all via a handful of delegated listeners):
 *   1. clicks/changes that start an AJAX call (XHR / jQuery / axios / fetch)
 *   2. links that navigate to another page
 *   3. native form submits
 *   4. downloads (link, form post or location change). The server flags
 *      attachment responses with a short-lived cookie (see
 *      App\Http\Middleware\FileDownloadSignal) so the loader stops the
 *      moment the file arrives, even though the page never unloads.
 *
 * Opt out per element:   data-no-loader
 * Custom overlay text:   data-loader-text="Generating report..."
 *
 * Manual use (rare):
 *   var stop = SmartLoader.start('Saving...');  ...  stop();
 *   SmartLoader.wrap(somePromise, 'Saving...');
 */
(function (window, document) {
    'use strict';

    if (window.SmartLoader) { return; }

    var CFG = {
        overlayDelay: 350,        // ms before the overlay appears
        minBusy: 300,             // a spinner stays at least this long (no flicker)
        actionWindow: 800,        // a request starting this soon after a click belongs to it
        failsafe: 60000,          // never leave the UI busy longer than this
        downloadFailsafe: 20000,  // ... for things that look like downloads
        unloadFailsafe: 12000,    // ... for navigations we only learn about at beforeunload
        cookie: 'smasa_dl',
        // Background chatter that must never trigger a loader.
        ignoreUrl: /(notification|heartbeat|keep-?alive|poll|ping|unread|push|realtime)/i,
        downloadUrl: /\.(xlsx?|csv|pdf|docx?|pptx?|zip|rar)(\?|#|$)|\/(download|export|template|print)/i
    };

    if (window.SmartLoaderConfig) {
        for (var k in window.SmartLoaderConfig) {
            if (Object.prototype.hasOwnProperty.call(window.SmartLoaderConfig, k)) { CFG[k] = window.SmartLoaderConfig[k]; }
        }
    }

    // ------------------------------------------------------------------ CSS
    var css =
        '#sl-bar{position:fixed;top:0;left:0;width:100%;height:3px;z-index:2147483000;pointer-events:none;' +
        'background:linear-gradient(90deg,#2f2ccb,#6c63ff);box-shadow:0 0 8px rgba(47,44,203,.55);' +
        'transform:scaleX(0);transform-origin:0 50%;opacity:0}' +
        '#sl-bar.sl-run{opacity:1;transform:scaleX(.9);transition:transform 12s cubic-bezier(.08,.6,.2,1)}' +
        '#sl-bar.sl-done{opacity:0;transform:scaleX(1);transition:transform .2s ease-out,opacity .35s ease .2s}' +
        '#sl-overlay{position:fixed;inset:0;z-index:2147482000;display:flex;align-items:center;justify-content:center;' +
        'background:rgba(255,255,255,.55);opacity:0;visibility:hidden;transition:opacity .15s ease,visibility 0s linear .15s;cursor:progress}' +
        '#sl-overlay.sl-show{opacity:1;visibility:visible;transition:opacity .15s ease}' +
        '.dark-mode #sl-overlay{background:rgba(0,0,0,.5)}' +
        '#sl-overlay .sl-pill{display:flex;align-items:center;gap:.7rem;padding:.7rem 1.1rem;border-radius:999px;' +
        'background:#fff;color:#1e293b;font:600 14px/1.2 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;' +
        'box-shadow:0 6px 24px rgba(15,23,42,.18)}' +
        '.dark-mode #sl-overlay .sl-pill{background:#1f2937;color:#e5e7eb}' +
        '.sl-spin,.sl-busy::after{display:inline-block;width:1em;height:1em;border:2px solid currentColor;' +
        'border-right-color:transparent;border-radius:50%;animation:sl-rot .6s linear infinite;box-sizing:border-box}' +
        '#sl-overlay .sl-spin{width:18px;height:18px;color:#2f2ccb}' +
        '.sl-busy{pointer-events:none!important;cursor:progress!important;opacity:.78}' +
        '.sl-busy::after{content:"";margin-left:.55em;vertical-align:-.15em}' +
        'input.sl-busy::after,select.sl-busy::after,textarea.sl-busy::after{display:none}' +
        '@keyframes sl-rot{to{transform:rotate(360deg)}}' +
        '@media (prefers-reduced-motion:reduce){#sl-bar,#sl-overlay{transition:none!important}' +
        '.sl-spin,.sl-busy::after{animation-duration:1.6s}}';

    var style = document.createElement('style');
    style.id = 'sl-style';
    style.appendChild(document.createTextNode(css));
    (document.head || document.documentElement).appendChild(style);

    // ------------------------------------------------------------- UI pieces
    var bar, overlay, overlayText, barTimer;

    function ensureUi() {
        if (bar || !document.body) { return !!bar; }
        bar = document.createElement('div');
        bar.id = 'sl-bar';
        overlay = document.createElement('div');
        overlay.id = 'sl-overlay';
        overlay.setAttribute('role', 'status');
        overlay.setAttribute('aria-live', 'polite');
        overlay.innerHTML = '<div class="sl-pill"><span class="sl-spin"></span><span class="sl-text">Please wait\u2026</span></div>';
        overlayText = overlay.querySelector('.sl-text');
        document.body.appendChild(bar);
        document.body.appendChild(overlay);
        return true;
    }

    function barStart() {
        if (!ensureUi()) { return; }
        clearTimeout(barTimer);
        bar.className = '';
        void bar.offsetWidth;            // restart the CSS transition
        bar.className = 'sl-run';
    }

    function barFinish() {
        if (!bar) { return; }
        bar.className = 'sl-done';
        clearTimeout(barTimer);
        barTimer = setTimeout(function () { bar.className = ''; }, 600);
    }

    // ------------------------------------------------------------------ Jobs
    var jobs = [];                       // active jobs
    var cookieTimer = null;
    var lastAction = null;               // {el, t, barOnly}
    var actionJobs = [];                 // jobs created from clicks/changes (ajax)

    function hasJob(job) { return jobs.indexOf(job) !== -1; }

    function refresh() {
        if (jobs.length) {
            if (!bar || bar.className !== 'sl-run') { barStart(); }
        } else {
            barFinish();
        }

        var showOverlay = false, text = null;
        for (var i = 0; i < jobs.length; i++) {
            if (jobs[i].overlayReady) { showOverlay = true; text = jobs[i].text || text; }
        }
        if (showOverlay && ensureUi()) {
            overlayText.textContent = text || 'Please wait\u2026';
            overlay.className = 'sl-show';
        } else if (overlay) {
            overlay.className = '';
        }

        // Cookie polling only runs while a download-capable job is active.
        var needCookie = jobs.some(function (j) { return j.kind !== 'ajax'; });
        if (needCookie && !cookieTimer) {
            cookieTimer = setInterval(checkDownloadCookie, 300);
        } else if (!needCookie && cookieTimer) {
            clearInterval(cookieTimer);
            cookieTimer = null;
        }
    }

    function markBusy(el) {
        if (!el || !el.classList || el.classList.contains('sl-busy')) { return false; }
        el.classList.add('sl-busy');
        el.setAttribute('aria-busy', 'true');
        return true;
    }

    function clearBusy(el) {
        if (!el || !el.classList) { return; }
        el.classList.remove('sl-busy');
        el.removeAttribute('aria-busy');
    }

    /**
     * kind: 'ajax' | 'nav' | 'form' | 'download'
     * opts: {el, text, overlay, failsafe, barOnly}
     */
    function startJob(kind, opts) {
        opts = opts || {};
        var job = {
            kind: kind, el: null, text: opts.text, t: Date.now(), n: 0,
            overlayReady: false, overlayTimer: null, failTimer: null, ending: false
        };
        if (opts.el && !opts.barOnly && markBusy(opts.el)) { job.el = opts.el; }

        if (opts.overlay) {
            job.overlayTimer = setTimeout(function () {
                if (hasJob(job)) { job.overlayReady = true; refresh(); }
            }, CFG.overlayDelay);
        }
        job.failTimer = setTimeout(function () { endJob(job, true); }, opts.failsafe || CFG.failsafe);

        jobs.push(job);
        refresh();
        return job;
    }

    function endJob(job, immediate) {
        if (!hasJob(job) || job.ending) { return; }
        job.ending = true;
        var wait = immediate ? 0 : Math.max(0, CFG.minBusy - (Date.now() - job.t));
        setTimeout(function () {
            var i = jobs.indexOf(job);
            if (i === -1) { return; }
            jobs.splice(i, 1);
            clearTimeout(job.overlayTimer);
            clearTimeout(job.failTimer);
            clearBusy(job.el);
            if (job.form) { job.form.__slBusy = false; }
            var a = actionJobs.indexOf(job);
            if (a !== -1) { actionJobs.splice(a, 1); }
            refresh();
        }, wait);
    }

    function endAll() {
        jobs.slice().forEach(function (j) { endJob(j, true); });
    }

    // ------------------------------------------------------ Download signal
    function readCookie() {
        return document.cookie.indexOf(CFG.cookie + '=') !== -1;
    }

    function clearCookie() {
        document.cookie = CFG.cookie + '=; Max-Age=0; path=/';
    }

    function checkDownloadCookie() {
        if (!readCookie()) { return; }
        clearCookie();
        jobs.slice().forEach(function (j) { if (j.kind !== 'ajax') { endJob(j); } });
    }

    // ------------------------------------------------------ Event helpers
    function closestControl(target) {
        return target && target.closest
            ? target.closest('button, a, input[type="button"], input[type="submit"], input[type="image"], [role="button"], .btn')
            : null;
    }

    function optedOut(el) {
        return !!(el && el.closest && el.closest('[data-no-loader]'));
    }

    function isToggle(el) {
        return !!(el && el.closest && el.closest('[data-toggle],[data-bs-toggle],[data-dismiss],[data-bs-dismiss]'));
    }

    function loaderText(el) {
        var holder = el && el.closest ? el.closest('[data-loader-text]') : null;
        return holder ? holder.getAttribute('data-loader-text') : null;
    }

    function isNavigatingLink(a, e) {
        if (!a || a.tagName !== 'A' || !a.hasAttribute('href')) { return false; }
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return false; }
        var raw = a.getAttribute('href') || '';
        if (!raw || raw.charAt(0) === '#' || /^(javascript|mailto|tel|sms|blob|data):/i.test(raw)) { return false; }
        if (a.target && a.target !== '_self') { return false; }
        if (isToggle(a)) { return false; }
        var url;
        try { url = new URL(a.href, window.location.href); } catch (err) { return false; }
        if (url.protocol !== 'http:' && url.protocol !== 'https:') { return false; }
        if (url.origin !== window.location.origin) { return true; }  // external site: still a page change
        if (url.hash && url.pathname === window.location.pathname && url.search === window.location.search) { return false; }
        return true;
    }

    // ----------------------------------------------- Clicks and page changes
    document.addEventListener('click', function (e) {
        var el = closestControl(e.target);

        // A control that is already busy swallows repeat clicks.
        if (el && el.classList.contains('sl-busy')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        if (!el || optedOut(el)) { lastAction = null; return; }

        lastAction = { el: el, t: Date.now(), barOnly: isToggle(el) };

        // After every page handler has had its say, was this a real navigation?
        setTimeout(function () {
            if (e.defaultPrevented || !isNavigatingLink(el, e)) { return; }
            var url = el.getAttribute('href');
            var looksLikeFile = el.hasAttribute('download') || CFG.downloadUrl.test(url);
            clearCookie();
            startJob(looksLikeFile ? 'download' : 'nav', {
                el: el,
                text: loaderText(el) || (looksLikeFile ? 'Preparing your file\u2026' : null),
                overlay: true,
                failsafe: looksLikeFile ? CFG.downloadFailsafe : CFG.failsafe
            });
        }, 0);
    }, true);

    document.addEventListener('change', function (e) {
        var t = e.target;
        if (!t || optedOut(t)) { return; }
        if (/^(select|input|textarea)$/i.test(t.tagName)) {
            lastAction = { el: t, t: Date.now(), barOnly: true };
        }
    }, true);

    // ------------------------------------------------------ Native form posts
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM' || optedOut(form)) { return; }

        if (form.__slBusy) {                 // double submit
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        var submitter = e.submitter || null;

        setTimeout(function () {
            if (e.defaultPrevented) { return; }          // handled via AJAX: XHR hook covers it
            if (form.target && form.target !== '_self') { return; }
            form.__slBusy = true;
            clearCookie();
            var job = startJob('form', {
                el: submitter,
                text: loaderText(submitter) || loaderText(form) || 'Working\u2026',
                overlay: true,
                failsafe: CFG.downloadFailsafe * 3
            });
            job.form = form;
        }, 0);
    }, true);

    // Programmatic navigation (location.href=..., form.submit()) only shows up here.
    window.addEventListener('beforeunload', function () {
        if (jobs.length) { return; }
        clearCookie();
        startJob('download', { overlay: true, failsafe: CFG.unloadFailsafe, text: 'Please wait\u2026' });
    });

    // Back/forward cache: the page returns exactly as we left it, so reset.
    window.addEventListener('pageshow', function (e) { if (e.persisted) { endAll(); } });

    // --------------------------------------------------------- AJAX tracking
    function attribute(url) {
        if (!lastAction || (Date.now() - lastAction.t) > CFG.actionWindow) { return null; }
        if (CFG.ignoreUrl.test(String(url || ''))) { return null; }

        var job = null;
        for (var i = 0; i < actionJobs.length; i++) {
            if (actionJobs[i].action === lastAction && hasJob(actionJobs[i]) && !actionJobs[i].ending) {
                job = actionJobs[i]; break;
            }
        }
        if (!job) {
            job = startJob('ajax', {
                el: lastAction.el,
                barOnly: lastAction.barOnly,
                text: loaderText(lastAction.el)
            });
            job.action = lastAction;
            actionJobs.push(job);
        }
        job.n++;
        return job;
    }

    function release(job) {
        if (!job) { return; }
        job.n--;
        if (job.n <= 0) {
            // brief grace so a chained follow-up request keeps the same spinner
            setTimeout(function () { if (job.n <= 0) { endJob(job); } }, 80);
        }
    }

    var xhrOpen = window.XMLHttpRequest && XMLHttpRequest.prototype.open;
    var xhrSend = window.XMLHttpRequest && XMLHttpRequest.prototype.send;

    if (xhrOpen && xhrSend) {
        XMLHttpRequest.prototype.open = function (method, url) {
            this.__slUrl = url;
            return xhrOpen.apply(this, arguments);
        };
        XMLHttpRequest.prototype.send = function () {
            var job = attribute(this.__slUrl);
            if (job) {
                this.addEventListener('loadend', function () { release(job); });
            }
            return xhrSend.apply(this, arguments);
        };
    }

    var nativeFetch = window.fetch;
    if (typeof nativeFetch === 'function') {
        window.fetch = function (input) {
            var url = typeof input === 'string' ? input : (input && input.url);
            var job = attribute(url);
            var p = nativeFetch.apply(this, arguments);
            if (job) {
                var done = function () { release(job); };
                p.then(done, done);          // observe only; the caller still gets `p` untouched
            }
            return p;
        };
    }

    // ---------------------------------------------------------- Public API
    window.SmartLoader = {
        start: function (text) {
            var job = startJob('ajax', { text: text, overlay: true, barOnly: true });
            job.n = 1;
            return function () { job.n = 0; endJob(job); };
        },
        wrap: function (promise, text) {
            var stop = window.SmartLoader.start(text);
            var done = function () { stop(); };
            promise.then(done, done);
            return promise;
        },
        stopAll: endAll
    };
})(window, document);
