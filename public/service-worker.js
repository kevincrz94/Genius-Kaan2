const CACHE_NAME = 'genius-kaan-v4';
const APP_SHELL = [
    '/icon.svg',
    '/css/public.css',
    '/assets/css/custom.css',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/common/favicon.png',
    '/common/light-logo.png',
    '/manifest.json',
    '/manifest.webmanifest',
    '/offline.html',
    '/screenshots/desktop-dashboard.png',
    '/screenshots/mobile-simulators.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => Promise.all(
            APP_SHELL.map(url => cache.add(url).catch(() => null))
        ))
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') {
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    event.respondWith(
        caches.match(event.request).then(cached => cached || fetch(event.request).then(response => {
            if (!response || response.status !== 200 || response.type !== 'basic') {
                return response;
            }

            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(event.request, responseToCache));

            return response;
        }).catch(() => cached))
    );
});
