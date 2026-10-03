const CACHE_NAME = 'sigtrans-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Passthrough básico - no cachea nada todavía, solo satisface el requisito de PWA instalable
    event.respondWith(fetch(event.request));
});