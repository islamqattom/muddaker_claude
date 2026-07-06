/**
 * Push Notification Initialization
 *
 * Responsibilities:
 *  - Register the service worker at /sw.js
 *  - Expose a clean API: window.PushNotifications.{status, enable, disable, test}
 *  - Auto-subscribe after login (but NOT silently on every page) — the
 *    first time we see the logged-in user with permission 'default' we
 *    prompt; subsequent visits with 'granted' just re-attach the
 *    subscription silently.
 *
 * Cross-browser notes:
 *  - iOS Safari (16.4+) supports Web Push ONLY when the page is installed
 *    to the Home Screen as a PWA. We detect this and tell the user.
 *  - Firefox, Chrome, Edge, Samsung Internet work out of the box.
 */

(function () {
    'use strict';

    const SUBSCRIBE_URL = '/push/subscribe';
    const UNSUBSCRIBE_URL = '/push/unsubscribe';
    const STATUS_URL = '/push/status';
    const SW_PATH = '/sw.js';
    const AUTO_PROMPT_FLAG = 'quran_push_auto_prompted';

    let swRegistration = null;
    let autoPromptedThisSession = false;

    // ============= Utilities =============

    function getVapidKey() {
        const meta = document.querySelector('meta[name="vapid-public-key"]');
        return meta ? meta.content.trim() : '';
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function isLoggedIn() {
        return !!document.querySelector('meta[name="user-id"]');
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function isIOSPWA() {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        // iOS PWA: standalone mode (added to home screen) — push works there.
        return isIOS && window.navigator.standalone === true;
    }

    function isIOSBrowser() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream && !window.navigator.standalone;
    }

    function getDeviceName() {
        const ua = navigator.userAgent;
        if (/iPhone/.test(ua)) return 'iPhone';
        if (/iPad/.test(ua)) return 'iPad';
        if (/Android.*Mobile/.test(ua)) return 'Android Phone';
        if (/Android/.test(ua)) return 'Android Tablet';
        if (/Windows/.test(ua)) return 'Windows';
        if (/Mac OS X/.test(ua)) return 'Mac';
        if (/Linux/.test(ua)) return 'Linux';
        return 'Unknown Device';
    }

    // ============= Subscribing / Unsubscribing =============

    async function ensureRegistration() {
        if (swRegistration) return swRegistration;
        if (!('serviceWorker' in navigator)) {
            throw new Error('UNSUPPORTED');
        }
        swRegistration = await navigator.serviceWorker.ready;
        return swRegistration;
    }

    async function subscribeToPush() {
        const registration = await ensureRegistration();
        if (!('PushManager' in window)) {
            throw new Error('UNSUPPORTED');
        }
        const vapidKey = getVapidKey();
        if (!vapidKey) {
            throw new Error('NO_VAPID');
        }

        // Always (re)create the subscription so we get a fresh endpoint if
        // the previous one expired.
        const existing = await registration.pushManager.getSubscription();
        if (existing) {
            await existing.unsubscribe();
        }

        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey),
        });

        const response = await fetch(SUBSCRIBE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                key_p256dh: subscription.keys.p256dh,
                key_auth: subscription.keys.auth,
                device_name: getDeviceName(),
            }),
        });

        if (!response.ok) {
            throw new Error('SERVER_ERROR');
        }
        return await response.json();
    }

    async function unsubscribeFromPush() {
        const registration = await ensureRegistration();
        const subscription = await registration.pushManager.getSubscription();
        if (!subscription) return;

        await subscription.unsubscribe();
        await fetch(UNSUBSCRIBE_URL, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ endpoint: subscription.endpoint }),
        });
    }

    // ============= Status =============

    function getStatus() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            return { state: 'unsupported', message: 'المتصفح لا يدعم الإشعارات' };
        }
        if (typeof Notification === 'undefined') {
            return { state: 'unsupported', message: 'الإشعارات غير متوفرة' };
        }
        const permission = Notification.permission;
        if (permission === 'denied') {
            return { state: 'denied', message: 'الإشعارات محظورة. فعّلها من إعدادات المتصفح.' };
        }
        if (permission === 'granted') {
            return { state: 'granted', message: 'الإشعارات مفعّلة' };
        }
        return { state: 'default', message: 'الإشعارات غير مفعّلة بعد' };
    }

    // ============= Public API =============

    /**
     * Show the browser's permission prompt and, if granted, register a
     * push subscription. Returns a Promise resolving to the permission
     * state ('granted' | 'denied' | 'default' | 'unsupported').
     */
    async function enable() {
        try {
            if (isIOSBrowser() && !isIOSPWA()) {
                throw new Error('IOS_NOT_PWA');
            }
            await ensureRegistration();

            // This is the only call that actually shows the browser prompt.
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                return permission;
            }

            await subscribeToPush();
            return 'granted';
        } catch (e) {
            console.warn('[Push] enable failed:', e.message);
            if (e.message === 'IOS_NOT_PWA') return 'ios-not-pwa';
            if (e.message === 'UNSUPPORTED') return 'unsupported';
            if (e.message === 'NO_VAPID') return 'no-vapid';
            return 'error';
        }
    }

    /**
     * Unregister the push subscription (the browser will stop delivering
     * pushes to this device). The user can re-enable later.
     */
    async function disable() {
        try {
            await unsubscribeFromPush();
            return true;
        } catch (e) {
            console.warn('[Push] disable failed:', e);
            return false;
        }
    }

    /**
     * Refresh the server-side subscription (re-attach on login / page load
     * when permission is already granted). Silent — no browser prompt.
     */
    async function syncIfPermitted() {
        if (Notification.permission !== 'granted') return false;
        if (!isLoggedIn()) return false;
        try {
            await subscribeToPush();
            return true;
        } catch (e) {
            console.warn('[Push] sync failed:', e);
            return false;
        }
    }

    /**
     * Auto-prompt once per session for logged-in users. We use a flag
     * stored in localStorage so a single user isn't re-prompted on every
     * page load (the prompt is intrusive). Users can always go to the
     * settings page to enable manually.
     */
    function maybeAutoPrompt() {
        if (autoPromptedThisSession) return;
        if (!isLoggedIn()) return;
        if (isIOSBrowser() && !isIOSPWA()) return; // iOS Safari — user must add to home screen first
        if (Notification.permission !== 'default') return;
        if (localStorage.getItem(AUTO_PROMPT_FLAG) === '1') return;

        autoPromptedThisSession = true;
        localStorage.setItem(AUTO_PROMPT_FLAG, '1');

        // Wait a tick so the page settles, then show the prompt.
        setTimeout(() => {
            enable();
        }, 3000);
    }

    // ============= Init =============

    async function init() {
        if (!('serviceWorker' in navigator)) {
            console.log('[Push] Service workers not supported');
            return;
        }
        try {
            const registration = await navigator.serviceWorker.register(SW_PATH);
            swRegistration = registration;
            // If the user is already logged in and already granted permission,
            // just make sure the server has a fresh subscription entry.
            await syncIfPermitted();
            // Try a one-time auto-prompt for new logged-in users.
            maybeAutoPrompt();
        } catch (e) {
            console.warn('[Push] SW registration failed:', e);
        }
    }

    // Expose the API globally.
    window.PushNotifications = {
        enable,
        disable,
        getStatus,
        syncIfPermitted,
        isIOSBrowser,
        isIOSPWA,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
