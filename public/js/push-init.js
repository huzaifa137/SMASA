(function () {
    console.log('[PushInit] Script started');

    const vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
    if (!vapidMeta) {
        console.error('[PushInit] MISSING: <meta name="vapid-public-key"> not found in page HTML');
        return;
    }

    const VAPID_PUBLIC_KEY = vapidMeta.content;
    console.log('[PushInit] VAPID key found:', VAPID_PUBLIC_KEY.substring(0, 20) + '...');

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
    }

    if (!('serviceWorker' in navigator)) {
        console.error('[PushInit] Service Workers NOT supported in this browser');
        return;
    }
    if (!('PushManager' in window)) {
        console.error('[PushInit] PushManager NOT supported in this browser');
        return;
    }

    console.log('[PushInit] Registering service worker at /sw.js');

    navigator.serviceWorker.register('/sw.js').then(function (reg) {
        console.log('[PushInit] Service worker registered OK, scope:', reg.scope);

        return Notification.requestPermission().then(function (permission) {
            console.log('[PushInit] Notification permission:', permission);

            if (permission !== 'granted') {
                console.warn('[PushInit] Permission not granted — stopping here');
                return;
            }

            return reg.pushManager.getSubscription().then(function (existing) {
                if (existing) {
                    console.log('[PushInit] Existing subscription found, sending to server');
                    return sendToServer(existing);
                }

                console.log('[PushInit] No existing subscription, creating new one...');
                return reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
                }).then(function(sub) {
                    console.log('[PushInit] New subscription created, sending to server');
                    return sendToServer(sub);
                });
            });
        });
    }).catch(function(err) {
        console.error('[PushInit] Service worker registration FAILED:', err);
    });

    function sendToServer(subscription) {
        const key   = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');
        const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            console.error('[PushInit] MISSING: <meta name="csrf-token"> not found');
            return;
        }

        const payload = {
            endpoint:        subscription.endpoint,
            key:             key   ? btoa(String.fromCharCode(...new Uint8Array(key)))   : null,
            token:           token ? btoa(String.fromCharCode(...new Uint8Array(token))) : null,
            contentEncoding: contentEncoding,
        };

        console.log('[PushInit] Sending to /notifications/push/subscribe, endpoint starts:', subscription.endpoint.substring(0, 40));

        fetch('/notifications/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.content,
            },
            body: JSON.stringify(payload),
        }).then(function(response) {
            console.log('[PushInit] Server response status:', response.status);
            return response.json();
        }).then(function(data) {
            console.log('[PushInit] Server response body:', data);
        }).catch(function(err) {
            console.error('[PushInit] Fetch to server FAILED:', err);
        });
    }
})();