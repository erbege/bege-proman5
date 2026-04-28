import './bootstrap';
import './firebase-init';

// Notification initialization
document.addEventListener('DOMContentLoaded', () => {
    if (window.userId && window.userId !== 'null') {
        console.log('[Notifications] DOMContentLoaded, initializing...');
        initializeNotifications();
    }
});

// For Livewire navigation
document.addEventListener('livewire:navigated', () => {
    if (window.userId && window.userId !== 'null') {
        console.log('[Notifications] Livewire navigated, re-initializing...');
        initializeNotifications();
    }
});

function initializeNotifications() {
    const userId = window.userId;
    
    // Silently skip if guest (login page, etc)
    if (!userId || userId === 'null' || userId === undefined) {
        return;
    }

    console.log('[Notifications] Initializing for User ID:', userId);

    // 1. Initialize FCM
    if (window.PROMAN_FCM) {
        console.log('[Notifications] Initializing FCM...');
        window.PROMAN_FCM.initializeFcm().then(success => {
            if (success) console.log('[Notifications] FCM Initialized successfully');
            else console.error('[Notifications] FCM Initialization failed');
        });
    }

    // 2. Initialize Pusher (Echo)
    if (window.Echo) {
        const channelName = `App.Models.User.${userId}`;
        console.log('[Notifications] Subscribing to private channel:', channelName);
        
        window.Echo.private(channelName)
            .notification((notification) => {
                console.log('[Notifications] Received notification via Pusher:', notification);
                
                // Refresh Livewire Dropdown
                if (window.Livewire) {
                    console.log('[Notifications] Dispatching refreshNotifications to Livewire');
                    window.Livewire.dispatch('refreshNotifications');
                }

                // Show Toast
                if (window.Swal) {
                    window.Swal.fire({
                        title: notification.title || 'Notifikasi Baru',
                        text: notification.message || '',
                        icon: 'info',
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', window.Swal.stopTimer)
                            toast.addEventListener('mouseleave', window.Swal.resumeTimer)
                            toast.addEventListener('click', () => {
                                if (notification.url) window.location.href = notification.url;
                            });
                        }
                    });
                }
            });
            
        // Listen for connection status
        window.Echo.connector.pusher.connection.bind('state_change', (states) => {
            console.log('[Notifications] Pusher Connection State:', states.current);
        });
    } else {
        console.error('[Notifications] Laravel Echo not found');
    }
}
