import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const token = token();

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    wsHost: import.meta.env.VITE_PUSHER_HOST,
    wsPort: import.meta.env.VITE_PUSHER_PORT,
    wssPort: import.meta.env.VITE_PUSHER_PORT,
    forceTLS: false, 
    enabledTransports: ['ws', 'wss'],

    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    },
});

// قناة طلب العميل
window.Echo.private(`orders.${orderId}`)
    .listen('.order.created', (data) => {
        console.log('Order created:', data);
    })
    .listen('.order.status.changed', (data) => {
        console.log('Order status changed:', data);
    });

// قناة الأدمن
window.Echo.private('admin.orders')
    .listen('.order.created', (data) => {
        console.log('New order:', data);
    })
    .listen('.order.status.changed', (data) => {
        console.log('Order status changed:', data);
});

