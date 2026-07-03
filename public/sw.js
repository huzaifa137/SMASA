self.addEventListener('push', function (event) {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'SMASA Notification';
    const options = {
        body: data.body || '',
        icon: data.icon || 'https://smasa-academics.com/assets/images/brand/logo.png',
        badge: data.badge || 'https://smasa-academics.com/assets/images/brand/uplogolight.png',
        vibrate: [200, 100, 200],
        data: { url: data.url || '/' },
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const url = event.notification.data.url || '/';
    event.waitUntil(clients.openWindow(url));
});