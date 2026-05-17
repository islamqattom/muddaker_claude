const CACHE_NAME = 'quran-pwa-v3';
const urlsToCache = [
    '/images/muddaker-logo-main-nobg.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
    'https://code.jquery.com/jquery-3.7.1.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap'
];

// Install event - cache resources with better error handling
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                // Use Promise.allSettled to continue even if some URLs fail
                return Promise.allSettled(
                    urlsToCache.map(url =>
                        cache.add(url).catch(err => {
                            console.warn('Failed to cache:', url);
                            return null;
                        })
                    )
                );
            })
    );
    self.skipWaiting();
});

// Activate event - clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Push event - show notification when push message is received
self.addEventListener('push', event => {
    let data = {
        title: 'مشروع مدكر القرآني',
        body: 'لديك إشعار جديد',
        icon: '/images/muddaker-logo-main-nobg.png',
        link: '/',
    };

    try {
        if (event.data) {
            const payload = event.data.json();
            data = {
                title: payload.title || data.title,
                body: payload.body || data.body,
                icon: payload.icon || data.icon,
                link: payload.link || payload.url || data.link,
                tag: payload.tag || 'quran-notification',
                requireInteraction: payload.requireInteraction || false,
            };
        }
    } catch (e) {
        // Fall back to text if JSON parsing fails
        if (event.data) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon,
        tag: data.tag || 'quran-notification',
        requireInteraction: data.requireInteraction || false,
        data: { url: data.link },
        vibrate: [200, 100, 200],
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification click event - open link when notification is clicked
self.addEventListener('notificationclick', event => {
    event.notification.close();

    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
            // Focus existing window if open
            for (const client of clientList) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            // Open new window
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

// Fetch event - different strategies for different resources
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Skip API requests - let them go directly to network
    if (url.pathname.startsWith('/api/')) {
        return;
    }

    const isNavigation = event.request.mode === 'navigate';

    // For navigation requests (page loads), always go to network
    // This ensures auth redirects work properly
    if (isNavigation) {
        event.respondWith(fetch(event.request));
        return;
    }

    // Skip caching for HTML documents
    if (event.request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(fetch(event.request));
        return;
    }

    // Stale-while-revalidate for CDN resources (CSS, JS, fonts)
    if (url.hostname.includes('cdn.') || url.hostname.includes('fonts.') || url.hostname.includes('jquery')) {
        event.respondWith(
            caches.open(CACHE_NAME).then(cache => {
                return cache.match(event.request).then(cached => {
                    const fetchPromise = fetch(event.request).then(response => {
                        // Only cache valid responses
                        if (response && response.status === 200) {
                            cache.put(event.request, response.clone());
                        }
                        return response;
                    }).catch(() => cached || fetch(event.request));
                    return cached || fetchPromise;
                });
            })
        );
        return;
    }

    // Network-first for local static resources (fall back to cache)
    // Only cache GET requests — the Cache API does not support POST/PUT/DELETE
    if (event.request.method !== 'GET') {
        event.respondWith(fetch(event.request));
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(response => {
                if (response && response.status === 200) {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME)
                        .then(cache => cache.put(event.request, responseToCache));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});
