(function () {
    // ─── tiny visible toast so you can see what's happening without DevTools ───
    function toast(msg, color) {
        var el = document.createElement('div');
        el.textContent = '🔔 ' + msg;
        el.style.cssText = [
            'position:fixed','bottom:20px','right:20px','z-index:99999',
            'background:' + (color || '#333'),'color:#fff','padding:10px 16px',
            'border-radius:8px','font-size:13px','max-width:320px',
            'box-shadow:0 4px 12px rgba(0,0,0,.3)','word-break:break-word'
        ].join(';');
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 7000);
    }

    // ─── check browser support ────────────────────────────────────────────────
    if (!('serviceWorker' in navigator)) {
        console.error('[PushInit] Service Workers NOT supported');
        return;
    }
    if (!('PushManager' in window)) {
        console.error('[PushInit] PushManager NOT supported');
        return;
    }

    // ─── find VAPID key ───────────────────────────────────────────────────────
    var vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
    if (!vapidMeta || !vapidMeta.content) {
        console.error('[PushInit] vapid-public-key meta missing');
        return;
    }
    var VAPID_PUBLIC_KEY = vapidMeta.content;

    // ─── find CSRF token ──────────────────────────────────────────────────────
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta || !csrfMeta.content) {
        console.error('[PushInit] csrf-token meta missing');
        toast('Push setup error: CSRF token missing', '#c0392b');
        return;
    }
    var CSRF_TOKEN = csrfMeta.content;

    // ─── helpers ──────────────────────────────────────────────────────────────
    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = atob(base64);
        var outputArray = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Safe base64 encode — avoids spread-operator stack overflow on large ArrayBuffers
    function arrayBufferToBase64(buffer) {
        var bytes = new Uint8Array(buffer);
        var binary = '';
        for (var i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary);
    }

    // ─── main flow ────────────────────────────────────────────────────────────
    navigator.serviceWorker.register('/sw.js')
        .then(function (reg) {
            console.log('[PushInit] SW registered, scope:', reg.scope);

            // If permission already granted, skip the prompt
            if (Notification.permission === 'granted') {
                return subscribeAndSend(reg);
            }
            if (Notification.permission === 'denied') {
                console.warn('[PushInit] Notifications blocked by user');
                return;
            }

            // Ask for permission
            return Notification.requestPermission().then(function (permission) {
                console.log('[PushInit] Permission result:', permission);
                if (permission !== 'granted') {
                    console.warn('[PushInit] Permission not granted');
                    return;
                }
                return subscribeAndSend(reg);
            });
        })
        .catch(function (err) {
            console.error('[PushInit] SW registration failed:', err);
            toast('Push setup failed: SW registration error (' + err.message + ')', '#c0392b');
        });

    function subscribeAndSend(reg) {
        return reg.pushManager.getSubscription()
            .then(function (existing) {
                if (existing) {
                    console.log('[PushInit] Reusing existing subscription');
                    return sendToServer(existing, false); // silent re-sync, no toast
                }
                console.log('[PushInit] Creating new push subscription...');
                return reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
                }).then(function (sub) {
                    console.log('[PushInit] Subscription created:', sub.endpoint.substring(0, 50));
                    return sendToServer(sub, true); // first-time subscribe, show toast
                });
            })
            .catch(function (err) {
                console.error('[PushInit] Subscribe failed:', err);
                toast('Push subscribe failed: ' + err.message, '#c0392b');
            });
    }

    function sendToServer(subscription, showToast) {
        var key   = subscription.getKey ? subscription.getKey('p256dh') : null;
        var token = subscription.getKey ? subscription.getKey('auth')   : null;
        var contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

        var payload = {
            endpoint:        subscription.endpoint,
            key:             key   ? arrayBufferToBase64(key)   : null,
            token:           token ? arrayBufferToBase64(token) : null,
            contentEncoding: contentEncoding,
        };

        console.log('[PushInit] POSTing to /notifications/push/subscribe');

        return fetch('/notifications/push/subscribe', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin',   // ← ensures session cookie is sent
        })
        .then(function (response) {
            console.log('[PushInit] Server HTTP status:', response.status);
            return response.text().then(function (text) {
                console.log('[PushInit] Server response body:', text);
                if (response.ok) {
                    if (showToast) {
                        toast('Push notifications enabled ✓', '#27ae60');
                    }
                } else {
                    toast('Push subscribe failed (HTTP ' + response.status + '): ' + text.substring(0, 120), '#c0392b');
                }
            });
        })
        .catch(function (err) {
            console.error('[PushInit] Network error posting subscription:', err);
            toast('Push network error: ' + err.message, '#c0392b');
        });
    }
})();