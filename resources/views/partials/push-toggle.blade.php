{{--
    Notifications ON/OFF button (Web Push), shared by the staff header and the Parent Portal.

    Required variables:
      $pushSubscribeUrl   POST  – save this device's subscription
      $pushUnsubscribeUrl POST  – remove it
      $pushStatusUrl      POST  – ask the server whether this device is registered

    Why a button and not the bell: the bell opens the notification list. This
    button is a separate control whose label/colour always shows the REAL state
    (browser permission + browser subscription + server record), locks while a
    request is running, and explains itself when push can't work on a device.
--}}
<div id="pushStatusWrap" class="push-toggle-wrap">
    <button type="button" id="pushToggleBtn" class="push-toggle is-loading" data-state="loading" disabled
        aria-live="polite" title="Checking notification status…">
        <i class="fas fa-spinner fa-spin pt-icon"></i>
        <span class="pt-prefix">Notifications: </span><span class="pt-label">Checking…</span>
        <span class="pt-track"><span class="pt-knob"></span></span>
    </button>
</div>

<style>
    .push-toggle-wrap { margin-left: 12px; display: flex; align-items: center; }
    .push-toggle { display: inline-flex; align-items: center; gap: 8px; border: 1.5px solid #d4d4d8; background: #fff; color: #3f3f46; border-radius: 999px; padding: 5px 10px 5px 12px; font-size: 12.5px; font-weight: 700; line-height: 1; cursor: pointer; white-space: nowrap; transition: background .15s, border-color .15s, color .15s; }
    .push-toggle:disabled { cursor: progress; opacity: .75; }
    .push-toggle .pt-track { position: relative; width: 34px; height: 18px; border-radius: 999px; background: #d4d4d8; transition: background .15s; flex: none; }
    .push-toggle .pt-knob { position: absolute; top: 2px; left: 2px; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform .15s; box-shadow: 0 1px 2px rgba(0,0,0,.3); }
    .push-toggle.is-on { border-color: #10b981; color: #047857; background: rgba(16,185,129,.08); }
    .push-toggle.is-on .pt-track { background: #10b981; }
    .push-toggle.is-on .pt-knob { transform: translateX(16px); }
    .push-toggle.is-off { border-color: #f59e0b; color: #b45309; background: rgba(245,158,11,.08); }
    .push-toggle.is-blocked { border-color: #ef4444; color: #b91c1c; background: rgba(239,68,68,.07); }
    .push-toggle.is-blocked .pt-track { background: #fca5a5; }
    .push-toggle.is-unsupported { border-color: #a1a1aa; color: #71717a; background: #f4f4f5; }
    @media (max-width: 576px) { .push-toggle .pt-label { font-size: 11.5px; } .push-toggle-wrap { margin-left: 6px; } }
</style>

<script>
    (function () {
        var btn = document.getElementById('pushToggleBtn');
        if (!btn) return;
        var icon = btn.querySelector('.pt-icon');
        var label = btn.querySelector('.pt-label');

        var URLS = {
            subscribe: @json($pushSubscribeUrl),
            unsubscribe: @json($pushUnsubscribeUrl),
            status: @json($pushStatusUrl)
        };
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
        var CSRF = csrfMeta ? csrfMeta.content : '';
        var VAPID = vapidMeta ? vapidMeta.content : '';

        var reg = null;
        var busy = false;

        function toast(msg, color) {
            var el = document.createElement('div');
            el.textContent = msg;
            el.style.cssText = 'position:fixed;bottom:20px;right:20px;left:20px;max-width:340px;margin-left:auto;z-index:99999;background:' + (color || '#333') + ';color:#fff;padding:12px 16px;border-radius:10px;font-size:14px;box-shadow:0 4px 14px rgba(0,0,0,.3);word-break:break-word';
            document.body.appendChild(el);
            setTimeout(function () { el.remove(); }, 7000);
        }

        function paint(state, tip) {
            btn.dataset.state = state;
            btn.className = 'push-toggle is-' + state;
            btn.disabled = (state === 'loading');
            var map = {
                on:          ['fa-bell', 'ON', 'Notifications are ON for this device — click to turn OFF'],
                off:         ['fa-bell-slash', 'OFF', 'Notifications are OFF for this device — click to turn ON'],
                blocked:     ['fa-ban', 'BLOCKED', 'Blocked in browser settings — click for help'],
                unsupported: ['fa-circle-info', 'not available', 'This browser/device can\u2019t receive notifications — click for help'],
                loading:     ['fa-spinner fa-spin', 'Please wait…', 'Working…']
            };
            var m = map[state] || map.off;
            icon.className = 'fas pt-icon ' + m[0];
            label.textContent = m[1];
            btn.title = tip || m[2];
        }

        function api(url, body) {
            return fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(body || {})
            }).then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (json) {
                    if (!res.ok) {
                        var msg = json.error || json.message ||
                            (res.status === 419 ? 'Your session expired — refresh the page and try again.' :
                             res.status === 401 ? 'You are logged out — please log in again.' :
                             res.status === 403 ? 'You do not have permission to change notifications.' :
                             'Server error (' + res.status + ').');
                        var err = new Error(msg); err.status = res.status; throw err;
                    }
                    return json;
                });
            });
        }

        function b64(buf) {
            var bytes = new Uint8Array(buf), s = '';
            for (var i = 0; i < bytes.byteLength; i++) s += String.fromCharCode(bytes[i]);
            return btoa(s);
        }
        function keyToBytes(base64String) {
            var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            var raw = atob(base64), out = new Uint8Array(raw.length);
            for (var i = 0; i < raw.length; ++i) out[i] = raw.charCodeAt(i);
            return out;
        }

        function saveToServer(sub) {
            var k = sub.getKey('p256dh'), a = sub.getKey('auth');
            return api(URLS.subscribe, {
                endpoint: sub.endpoint,
                key: k ? b64(k) : null,
                token: a ? b64(a) : null,
                contentEncoding: (window.PushManager && PushManager.supportedContentEncodings || ['aesgcm'])[0]
            });
        }

        // Real state = browser permission + browser subscription + server record.
        function sync() {
            if (Notification.permission === 'denied') { paint('blocked'); return Promise.resolve(); }
            return reg.pushManager.getSubscription().then(function (sub) {
                if (!sub) { paint('off'); return; }
                return api(URLS.status, { endpoint: sub.endpoint }).then(function (st) {
                    if (st.registered) { paint('on'); return; }
                    // Browser is subscribed but the server lost/never saved it → silently repair.
                    return saveToServer(sub).then(function () { paint('on'); });
                });
            }).catch(function (err) {
                paint('off', 'Could not check status: ' + err.message);
            });
        }

        function turnOn() {
            return Notification.requestPermission().then(function (perm) {
                if (perm !== 'granted') {
                    paint(perm === 'denied' ? 'blocked' : 'off');
                    if (perm === 'denied') toast('Notifications were blocked. Allow them in your browser\u2019s site settings, then click the button again.', '#e74c3c');
                    return;
                }
                return reg.pushManager.getSubscription().then(function (existing) {
                    // A leftover subscription made with different keys makes subscribe() throw — clear it first.
                    return existing ? existing.unsubscribe().catch(function () {}) : null;
                }).then(function () {
                    return reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: keyToBytes(VAPID) });
                }).then(function (sub) {
                    return saveToServer(sub).catch(function (err) {
                        // Server refused → undo the browser side so the button never lies.
                        return sub.unsubscribe().catch(function () {}).then(function () { throw err; });
                    });
                }).then(function () {
                    paint('on');
                    toast('\uD83D\uDD14 Notifications are ON for this device.', '#10b981');
                });
            });
        }

        function turnOff() {
            return reg.pushManager.getSubscription().then(function (sub) {
                if (!sub) { paint('off'); return; }
                var endpoint = sub.endpoint;
                // Tell the server FIRST; only then drop the browser subscription.
                return api(URLS.unsubscribe, { endpoint: endpoint }).then(function () {
                    return sub.unsubscribe();
                }).then(function () {
                    paint('off');
                    toast('\uD83D\uDD15 Notifications are OFF for this device.', '#555');
                });
            });
        }

        btn.addEventListener('click', function () {
            var state = btn.dataset.state;

            if (state === 'unsupported') {
                var ios = /iphone|ipad|ipod/i.test(navigator.userAgent);
                toast(ios
                    ? 'On iPhone/iPad, notifications only work after you tap Share → \u201CAdd to Home Screen\u201D and open SMASA from that icon (iOS 16.4+).'
                    : 'This browser can\u2019t receive push notifications. Try Chrome, Edge or Firefox, over HTTPS.', '#52525b');
                return;
            }
            if (state === 'blocked') {
                toast('Notifications are blocked. Tap the padlock/site-info icon in the address bar, set Notifications to Allow, then reload.', '#e74c3c');
                return;
            }
            if (busy || !reg) return;

            busy = true;
            var was = state;
            paint('loading');
            (was === 'on' ? turnOff() : turnOn()).catch(function (err) {
                console.error('[PushToggle]', err);
                toast((was === 'on' ? 'Could not turn notifications OFF: ' : 'Could not turn notifications ON: ') + err.message, '#e74c3c');
                return sync();
            }).then(function () { busy = false; });
        });

        // ── boot ──
        var insecure = !window.isSecureContext;
        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window) || insecure) {
            paint('unsupported', insecure ? 'Notifications need a secure (https) connection' : null);
            return;
        }
        if (!VAPID || !CSRF) {
            paint('unsupported', 'Notifications are not configured on the server (missing VAPID key).');
            return;
        }

        navigator.serviceWorker.register('/sw.js').then(function () {
            return navigator.serviceWorker.ready;
        }).then(function (r) {
            reg = r;
            return sync();
        }).catch(function (err) {
            console.error('[PushToggle] service worker failed', err);
            paint('unsupported', 'Could not start the notification service: ' + err.message);
        });

        // Keep the label truthful if the user changes permission in another tab / comes back later.
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden && reg && !busy) sync();
        });
    })();
</script>
