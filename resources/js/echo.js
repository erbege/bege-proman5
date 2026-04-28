import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const echoConfig = {
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: import.meta.env.VITE_PUSHER_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
};

if (import.meta.env.VITE_PUSHER_HOST) {
    echoConfig.wsHost = import.meta.env.VITE_PUSHER_HOST;
    echoConfig.wsPort = import.meta.env.VITE_PUSHER_PORT ?? 80;
    echoConfig.wssPort = import.meta.env.VITE_PUSHER_PORT ?? 443;
}

window.Echo = new Echo(echoConfig);
