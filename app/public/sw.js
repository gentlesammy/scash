const CACHE_NAME = 'scash-v2';
const ASSETS = [
    '/',
    '/css/styles.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    '/favicon.ico'
];

// Install service worker and cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(ASSETS);
        })
    );
    self.skipWaiting();
});

// Cache busting and activation
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// Intercept network requests to serve from network first, falling back to cache when offline
self.addEventListener('fetch', event => {
    // Only intercept GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(networkResponse => {
                // If the response is valid, update the cache for matching assets
                if (networkResponse && networkResponse.status === 200) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        const url = new URL(event.request.url);
                        // Cache if it is a static asset we track or the homepage navigation
                        if (ASSETS.includes(url.pathname) || ASSETS.includes(event.request.url)) {
                            cache.put(event.request, responseClone);
                        }
                    });
                }
                return networkResponse;
            })
            .catch(() => {
                // Network failed (offline), check cache
                return caches.match(event.request).then(cachedResponse => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Offline fallback: return cached homepage for navigation requests
                    if (event.request.mode === 'navigate') {
                        return caches.match('/');
                    }
                });
            })
    );
});
