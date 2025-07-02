// Update your bootstrap.js with better debugging
console.log("✅ Bootstrap Js Included...");

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from "laravel-echo";
import Pusher from 'pusher-js';
Pusher.logToConsole = true;
window.Pusher = Pusher;

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log("🔍 Auth Check:");
    console.log("Auth User ID:", window.authUserId);
    console.log("Is Authenticated:", window.isAuthenticated);
    console.log("CSRF Token:", document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

    // Check if user is authenticated before setting up Echo
    if (window.isAuthenticated && window.authUserId) {
        console.log("✅ Setting up Echo...");

        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            forceTLS: true,
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                withCredentials: true
            }
        });

        // Add connection event listeners RIGHT AFTER Echo initialization
        window.Echo.connector.pusher.connection.bind('connected', () => {
            console.log('✅ Pusher connected, Socket ID:', window.Echo.socketId());

            // Now that we're connected, test the auth endpoint with real socket ID
            testBroadcastingAuth();
        });

        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            console.log('❌ Pusher disconnected');
        });

        window.Echo.connector.pusher.connection.bind('connecting', () => {
            console.log('🔄 Pusher connecting...');
        });

        window.Echo.connector.pusher.connection.bind('failed', () => {
            console.log('❌ Pusher connection failed');
        });

        console.log("🔗 Attempting to join private channel: user." + window.authUserId);

        // FIRST: Test with public channel
        console.log("🧪 Testing with public channel first...");
        window.Echo.channel('test-public-channel')
            .listen('.TestEvent', (e) => {
                console.log('✅ PUBLIC CHANNEL WORKS! Event received:', e);
            });

        // THEN: Try private channel
        const channel = window.Echo.private(`user.${window.authUserId}`);

        // Add error handling for channel subscription
        channel.error((error) => {
            console.error("❌ Channel subscription error:", error);
        });

        // Log when successfully subscribed
        channel.subscribed(() => {
            console.log("✅ Successfully subscribed to channel: user." + window.authUserId);

            // Test if we can receive events by sending a test event
            console.log("🧪 Channel subscription confirmed - ready to receive events");

            // Log the actual channel name for debugging
            console.log("📡 Subscribed channel key:", `private-user.${window.authUserId}`);

            // Check if the channel exists in Pusher's channel list
            setTimeout(() => {
                const channels = window.Echo.connector.pusher.channels.all();
                const channelKey = `private-user.${window.authUserId}`;
                if (channels[channelKey]) {
                    console.log("✅ Channel found in Pusher channels list");
                    console.log("Channel subscribed status:", channels[channelKey].subscribed);
                } else {
                    console.error("❌ "+ channelKey +" Channel NOT found in Pusher channels list!");
                    console.log("Available channels:", Object.keys(channels));
                }
            }, 1000);
        });

        // Listen for job completed events (WITHOUT the dot since you have broadcastAs)
        channel.listen("JobCompleted", (e) => {
            console.log("✅ Job completed event received!", e);
            console.log("Message:", e.message);
            console.log("User ID:", e.userId);
        });

        // Global event listener
        window.Echo.connector.pusher.bind_global(function (eventName, data) {
            console.log("🔔 Global Event Received:", eventName, data);
        });

        console.log("Echo setup complete");

        // Function to test broadcasting auth with real socket ID
        function testBroadcastingAuth() {
            const socketId = window.Echo.socketId();
            if (socketId) {
                console.log('🧪 Testing broadcasting auth with Socket ID:', socketId);

                fetch('/broadcasting/auth', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        socket_id: socketId,
                        channel_name: `private-user.${window.authUserId}`
                    }),
                    credentials: 'include'
                })
                    .then(async response => {
                        console.log('🧪 Auth Test - Status:', response.status);
                        console.log('🧪 Auth Test - Headers:', Object.fromEntries(response.headers.entries()));

                        const text = await response.text();
                        console.log('🧪 Auth Test - Raw Response:', JSON.stringify(text));
                        console.log('🧪 Auth Test - Response Length:', text.length);
                        console.log('🧪 Auth Test - Is Empty:', text === '');

                        if (text) {
                            try {
                                const json = JSON.parse(text);
                                console.log('✅ Auth Test - Parsed JSON:', json);
                            } catch (e) {
                                console.error('❌ Auth Test - Not valid JSON:', text);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('❌ Auth Test - Error:', error);
                    });
            } else {
                console.warn('⚠️ No socket ID available yet for auth test');
            }
        }

    } else {
        console.error("❌ User not authenticated - cannot setup private channels");
        console.log("Auth User ID:", window.authUserId);
        console.log("Is Authenticated:", window.isAuthenticated);
    }
});
