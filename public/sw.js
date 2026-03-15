const CACHE = 'healthcare-balance-v1';
self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE).then(() => self.skipWaiting()));
});
self.addEventListener('activate', (e) => {
  e.waitUntil(caches.keys().then((keys) => Promise.all(
    keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))
  )).then(() => self.clients.claim()));
});
self.addEventListener('fetch', (e) => {
  e.respondWith(fetch(e.request).catch(() => caches.match(e.request)));
});
self.addEventListener('push', (e) => {
  const data = e.data?.json() || {};
  const opts = {
    body: data.body || 'Nouvelle notification',
    icon: '/icon-192.png',
    badge: '/icon-192.png',
    data: { url: data.url || '/' },
  };
  e.waitUntil(self.registration.showNotification(data.title || 'Healthcare Balance', opts));
});
self.addEventListener('notificationclick', (e) => {
  e.notification.close();
  const url = e.notification.data?.url || '/';
  e.waitUntil(clients.matchAll({ type: 'window' }).then((cs) => {
    if (cs.length) cs[0].navigate(url).focus();
    else clients.openWindow(url);
  }));
});
