/**
 * Firebase Cloud Messaging initialization for PROMAN5
 * Handles FCM token registration and foreground message handling
 */

import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage, isSupported } from 'firebase/messaging';

// Firebase configuration from environment variables
const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId: import.meta.env.VITE_FIREBASE_APP_ID
};

const VAPID_KEY = import.meta.env.VITE_FIREBASE_VAPID_KEY;

let messaging = null;
let isInitialized = false;

/**
 * Check if Firebase Messaging is supported in current browser
 */
export async function isFcmSupported() {
    try {
        return await isSupported();
    } catch (error) {
        console.warn('[FCM] Browser check failed:', error);
        return false;
    }
}

/**
 * Initialize Firebase app and messaging
 */
export async function initializeFirebase() {
    if (isInitialized) return messaging;

    // Check if config is available
    if (!firebaseConfig.apiKey || !firebaseConfig.projectId) {
        console.warn('[FCM] Firebase config not available');
        return null;
    }

    // Check browser support
    const supported = await isFcmSupported();
    if (!supported) {
        console.warn('[FCM] Firebase Messaging not supported in this browser');
        return null;
    }

    try {
        const app = initializeApp(firebaseConfig);
        messaging = getMessaging(app);
        isInitialized = true;
        console.log('[FCM] Firebase initialized successfully');
        return messaging;
    } catch (error) {
        console.error('[FCM] Failed to initialize Firebase:', error);
        return null;
    }
}

/**
 * Request notification permission and get FCM token
 */
export async function requestNotificationPermission() {
    if (!messaging) {
        messaging = await initializeFirebase();
        if (!messaging) return null;
    }

    try {
        const permission = await Notification.requestPermission();

        if (permission !== 'granted') {
            console.log('[FCM] Notification permission denied');
            return null;
        }

        // Register service worker
        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
        console.log('[FCM] Service Worker registered');

        // Get FCM token
        const token = await getToken(messaging, {
            vapidKey: VAPID_KEY,
            serviceWorkerRegistration: registration
        });

        if (token) {
            console.log('[FCM] Token received');
            return token;
        } else {
            console.warn('[FCM] No registration token available');
            return null;
        }
    } catch (error) {
        console.error('[FCM] Error getting token:', error);
        return null;
    }
}

/**
 * Register FCM token with backend
 */
export async function registerTokenWithBackend(token, deviceName = 'Web Browser', platform = 'web') {
    try {
        const response = await fetch('/api/fcm-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            credentials: 'include',
            body: JSON.stringify({
                token,
                device_name: deviceName,
                platform
            })
        });

        const data = await response.json();

        if (data.success) {
            console.log('[FCM] Token registered with backend');
            // Store token in localStorage for reference
            localStorage.setItem('fcm_token', token);
        } else {
            console.error('[FCM] Failed to register token:', data.message);
        }

        return data;
    } catch (error) {
        console.error('[FCM] Error registering token:', error);
        return { success: false, message: error.message };
    }
}

/**
 * Unregister FCM token from backend (on logout)
 */
export async function unregisterToken() {
    const token = localStorage.getItem('fcm_token');
    if (!token) return;

    try {
        await fetch('/api/fcm-token', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            credentials: 'include',
            body: JSON.stringify({ token })
        });

        localStorage.removeItem('fcm_token');
        console.log('[FCM] Token unregistered');
    } catch (error) {
        console.error('[FCM] Error unregistering token:', error);
    }
}

/**
 * Set up foreground message handler
 */
export function onForegroundMessage(callback) {
    if (!messaging) {
        console.warn('[FCM] Messaging not initialized');
        return;
    }

    onMessage(messaging, (payload) => {
        console.log('[FCM] Foreground message received:', payload);

        // Show toast notification
        if (callback) {
            callback(payload);
        }

        // Dispatch custom event for Livewire/Alpine components
        window.dispatchEvent(new CustomEvent('fcm-message', {
            detail: payload
        }));
    });
}

/**
 * Initialize FCM and request permission (call this after user login)
 */
export async function initializeFcm() {
    const messaging = await initializeFirebase();
    if (!messaging) return false;

    const token = await requestNotificationPermission();
    if (!token) return false;

    const result = await registerTokenWithBackend(token);

    if (result.success) {
        // Set up foreground message handler
        onForegroundMessage((payload) => {
            // Show browser notification for foreground messages too (optional)
            if (Notification.permission === 'granted') {
                const notification = new Notification(payload.notification?.title || 'PROMAN5', {
                    body: payload.notification?.body || '',
                    icon: '/favicon.ico',
                    data: payload.data
                });

                notification.onclick = () => {
                    window.focus();
                    if (payload.data?.url) {
                        window.location.href = payload.data.url;
                    }
                    notification.close();
                };
            }
        });
    }

    return result.success;
}

// Export for global access
window.PROMAN_FCM = {
    initializeFcm,
    requestNotificationPermission,
    unregisterToken,
    isFcmSupported
};
