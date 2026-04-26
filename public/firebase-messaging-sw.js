// Firebase Messaging Service Worker for PROMAN5
// This file must be placed in the public directory root

// Import Firebase scripts
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');

// Firebase configuration - will be populated from environment
// These values are injected during build process or can be set manually
const firebaseConfig = {
    apiKey: self.FIREBASE_API_KEY || '',
    authDomain: self.FIREBASE_AUTH_DOMAIN || '',
    projectId: self.FIREBASE_PROJECT_ID || '',
    storageBucket: self.FIREBASE_STORAGE_BUCKET || '',
    messagingSenderId: self.FIREBASE_MESSAGING_SENDER_ID || '',
    appId: self.FIREBASE_APP_ID || ''
};

// Initialize Firebase only if config is available
if (firebaseConfig.apiKey && firebaseConfig.projectId) {
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    // Handle background messages
    messaging.onBackgroundMessage((payload) => {
        console.log('[firebase-messaging-sw.js] Received background message', payload);

        const notificationTitle = payload.notification?.title || 'PROMAN5 Notification';
        const notificationOptions = {
            body: payload.notification?.body || '',
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: payload.data?.type || 'general',
            data: payload.data || {},
            requireInteraction: true,
            actions: [
                {
                    action: 'view',
                    title: 'Lihat'
                },
                {
                    action: 'close',
                    title: 'Tutup'
                }
            ]
        };

        return self.registration.showNotification(notificationTitle, notificationOptions);
    });

    // Handle notification click
    self.addEventListener('notificationclick', (event) => {
        console.log('[firebase-messaging-sw.js] Notification click', event);
        
        event.notification.close();

        if (event.action === 'close') {
            return;
        }

        // Get URL from notification data
        const url = event.notification.data?.url || '/dashboard';

        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true })
                .then((clientList) => {
                    // Check if there's already an open window
                    for (const client of clientList) {
                        if (client.url.includes(self.location.origin) && 'focus' in client) {
                            client.focus();
                            client.navigate(url);
                            return;
                        }
                    }
                    // Open new window if none exists
                    if (clients.openWindow) {
                        return clients.openWindow(url);
                    }
                })
        );
    });
} else {
    console.warn('[firebase-messaging-sw.js] Firebase config not available, push notifications disabled');
}
