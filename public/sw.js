// Service Worker for Quran System PWA
// Handles:
//  - Offline caching (install/activate/fetch)
//  - Push notifications with sender name, message preview, and avatar
//  - Notification click → focus existing tab or open chat URL

const CACHE_NAME = 'quran-pwa-v5';
const APP_ICON = '/images/muddaker-logo-main-nobg.png';
const APP_BADGE = '/images/muddaker-badge.png';

const STATIC_ASSETS = [
    APP_ICON,
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
    'https://code.jquery.com/jquery-3.7.1.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap',
];

// ============= Install / Activate =============

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache =>
            Promise.allSettled(
                STATIC_ASSETS.map(url =>
                    cache.add(url).catch(err => {
                        console.warn('[SW] Failed to cache:', url);
                    })
                )
            )
        )
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames =>
            Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            )
        )
    );
    self.clients.claim();
});

// ============= Push event =============

self.addEventListener('push', event => {
    const defaults = {
        title: 'نظام مدكر',
        body: 'لديك إشعار جديد',
        icon: APP_ICON,
        badge: APP_BADGE,
        link: '/',
        tag: 'quran-notification',
        type: 'unknown',
        requireInteraction: false,
    };

    let payload;
    try {
        payload = event.data ? event.data.json() : {};
    } catch (e) {
        payload = { body: event.data ? event.data.text() : defaults.body };
    }

    const data = { ...defaults, ...payload };

    // Build a richer title when the sender is known, e.g.
    //   "Ahmed أرسل لك رسالة"   (1:1)
    //   "Ahmed في مجموعة الآباء" (group)
    if (data.sender_name && data.type === 'message') {
        data.title = data.title || `${data.sender_name} أرسل لك رسالة`;
    }

    const options = {
        body: data.body,
        icon: data.icon || APP_ICON,
        badge: data.badge || APP_BADGE,
        tag: data.tag,
        dir: 'rtl',
        lang: 'ar',
        requireInteraction: !!data.requireInteraction,
        vibrate: data.type === 'message' ? [200, 100, 200, 100, 200] : [200, 100, 200],
        // The full payload is stashed on the Notification so the click
        // handler can read it back.
        data: {
            url: data.link,
            type: data.type,
            notification_id: data.notification_id,
            conversation_id: data.conversation_id,
            sender_id: data.sender_id,
            sender_name: data.sender_name,
            message_preview: data.message_preview,
        },
        actions: data.type === 'message' ? [
            { action: 'open', title: 'فتح' },
            { action: 'dismiss', title: 'إغلاق' },
        ] : [],
    };

    event.waitUntil(self.registration.showNotification(data.title, options));
});

// ============= Notification click =============

self.addEventListener('notificationclick', event => {
    event.notification.close();

    // User clicked the explicit "dismiss" action — just close.
    if (event.action === 'dismiss') {
        return;
    }

    const targetUrl = event.notification.data?.url || '/';

    event.waitUntil(
        self.clients
            .matchAll({ type: 'window', includeUncontrolled: true })
            .then(clientList => {
                // Try to focus any already-open tab/window for the same site.
                // We can't compare URLs exactly (trailing slashes, query params)
                // so we look for a same-origin window and steer it to the
                // target URL.
                for (const client of clientList) {
                    if (client.url.startsWith(self.location.origin)) {
                        if ('focus' in client) {
                            client.focus();
                        }
                        if ('navigate' in client) {
                            return client.navigate(targetUrl);
                        }
                        // Some browsers don't expose navigate() — open a
                        // message post instead.
                        client.postMessage({ type: 'navigate', url: targetUrl });
                        return;
                    }
                }
                if (self.clients.openWindow) {
                    return self.clients.openWindow(targetUrl);
                }
            })
    );
});

// ============= Message from page (for fallback focus-on-click) =============

self.addEventListener('message', event => {
    if (event.data && event.data.type === 'navigate' && event.data.url) {
        event.waitUntil(self.clients.openWindow(event.data.url));
    }
});

// ============= Fetch (offline caching) =============

self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Skip non-GET, API, and HTML — let those go to network.
    if (event.request.method !== 'GET') return;
    if (url.pathname.startsWith('/api/')) return;
    if (event.request.headers.get('accept')?.includes('text/html')) {
        return;
    }

    const isCDN = url.hostname.includes('cdn.')
        || url.hostname.includes('fonts.')
        || url.hostname.includes('jquery')
        || url.hostname.includes('cdnjs.');

    if (isCDN) {
        // Stale-while-revalidate for CDNs.
        event.respondWith(
            caches.open(CACHE_NAME).then(cache =>
                cache.match(event.request).then(cached => {
                    const fetchPromise = fetch(event.request)
                        .then(response => {
                            if (response && response.status === 200) {
                                cache.put(event.request, response.clone());
                            }
                            return response;
                        })
                        .catch(() => cached);
                    return cached || fetchPromise;
                })
            )
        );
        return;
    }

    // Network-first for local static resources, with cache fallback.
    event.respondWith(
        fetch(event.request)
            .then(response => {
                if (response && response.status === 200) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});
