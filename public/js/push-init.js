/**
 * Push Notification Initialization
 * - Registers the service worker
 * - Exposes requestNotificationPermission() for manual permission request
 * - Subscribes to push notifications via /push/subscribe
 */

(function () {
    'use strict';

    const PUSH_SUBSCRIBE_URL = '/push/subscribe';
    const PUSH_UNSUBSCRIBE_URL = '/push/unsubscribe';
    const SW_PATH = '/sw.js';

    let swRegistration = null;

    function getVapidKey() {
        const meta = document.querySelector('meta[name="vapid-public-key"]');
        return meta ? meta.content : '';
    }

    async function subscribeToPush(registration) {
        if (!('PushManager' in window)) {
            console.log('PushManager not supported');
            return false;
        }

        const vapidKey = getVapidKey();
        if (!vapidKey) {
            console.log('VAPID public key not configured');
            return false;
        }

        try {
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidKey),
            });

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
                return false;
            }
            return true;
        } catch (err) {
            console.log('Push subscription failed:', err);
            return false;
        }
    }

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

    function init() {
        if (!('serviceWorker' in navigator)) {
            console.log('Service Worker not supported');
            return;
        }

        navigator.serviceWorker.register(SW_PATH)
            .then(function (registration) {
                console.log('SW registered:', registration.scope);
                swRegistration = registration;

                if (typeof onServiceWorkerReady === 'function') {
                    onServiceWorkerReady(registration);
                }
            })
            .catch(function (error) {
                console.log('SW registration failed:', error);
            });
    }

    window.requestNotificationPermission = async function() {
        if (!('serviceWorker' in navigator)) {
            alert('المتصفح لا يدعم الإشعارات');
            return 'error';
        }

        if (!swRegistration) {
            try {
                swRegistration = await navigator.serviceWorker.ready;
            } catch (e) {
                alert('الخدمة غير متوفرة حالياً');
                return 'error';
            }
        }

        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            const success = await subscribeToPush(swRegistration);
            if (success) {
                const btn = document.querySelector('.notification-permission-btn');
                if (btn) {
                    btn.style.display = 'none';
                }
                return 'granted';
            }
            return 'error';
        }
        return permission;
    };

    window.unsubscribeFromNotifications = async function() {
        if (!swRegistration) {
            return;
        }
        await unsubscribeFromPush(swRegistration);
    };

    window.onServiceWorkerReady = function(registration) {
        const btn = document.querySelector('.notification-permission-btn');
        if (btn && Notification.permission === 'default') {
            btn.style.display = 'flex';
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();