/**
 * Push Notification Initialization
 * - Registers the service worker
 * - Requests notification permission
 * - Subscribes to push notifications via /push/subscribe
 */

(function () {
    'use strict';

    const PUSH_SUBSCRIBE_URL = '/push/subscribe';
    const PUSH_UNSUBSCRIBE_URL = '/push/unsubscribe';
    const SW_PATH = '/sw.js';

    // Get VAPID public key from meta tag
    function getVapidKey() {
        const meta = document.querySelector('meta[name="vapid-public-key"]');
        return meta ? meta.content : '';
    }

    // Subscribe to push notifications
    async function subscribeToPush(registration) {
        if (!('PushManager' in window)) {
            console.log('PushManager not supported');
            return;
        }

        const vapidKey = getVapidKey();
        if (!vapidKey) {
            console.log('VAPID public key not configured');
            return;
        }

        try {
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidKey),
            });

            // Send subscription to server
            const response = await fetch(PUSH_SUBSCRIBE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    key_p256dh: subscription.keys.p256dh,
                    key_auth: subscription.keys.auth,
                }),
            });

            if (!response.ok) {
                console.log('Push subscription failed on server');
            }
        } catch (err) {
            console.log('Push subscription failed:', err);
        }
    }

    // Unsubscribe from push notifications
    async function unsubscribeFromPush(registration) {
        try {
            const subscription = await registration.pushManager.getSubscription();
            if (subscription) {
                await subscription.unsubscribe();
                await fetch(PUSH_UNSUBSCRIBE_URL, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ endpoint: subscription.endpoint }),
                });
            }
        } catch (err) {
            console.log('Push unsubscription failed:', err);
        }
    }

    // Convert VAPID key from URL-safe base64 to Uint8Array
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Initialize when DOM is ready
    function init() {
        if (!('serviceWorker' in navigator)) {
            console.log('Service Worker not supported');
            return;
        }

        navigator.serviceWorker.register(SW_PATH)
            .then(async function (registration) {
                console.log('SW registered:', registration.scope);

                // Check current permission and subscribe if granted
                const permission = Notification.permission;
                if (permission === 'granted') {
                    await subscribeToPush(registration);
                } else if (permission === 'default') {
                    // Override requestPermission to auto-subscribe when user grants permission
                    const originalRequestPermission = Notification.requestPermission;
                    Notification.requestPermission = async function () {
                        const result = await originalRequestPermission.call(Notification);
                        if (result === 'granted') {
                            await subscribeToPush(registration);
                        }
                        return result;
                    };
                }
            })
            .catch(function (error) {
                console.log('SW registration failed:', error);
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
