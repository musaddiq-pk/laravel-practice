console.log("✅ Bootstrap Js Included...");

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from "laravel-echo";
import Pusher from 'pusher-js';
Pusher.logToConsole = true;
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        withCredentials: true // <-- important to send cookies for auth!
    }
});

window.Echo.private("user." + 1)
    .listen(".JobCompleted", (e) => {
        console.log("Job completed!", e.message);
    });

console.log("User authenticated?", window);
