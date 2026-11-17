
let socket = null;
const subscribedChannels = new Map();

function socketState(state, label) {
    document.querySelectorAll('[data-socket-state]').forEach(element => {
        const dot = element.querySelector('.dot');
        const labelElement = element.querySelector('[data-socket-label]');

        if (dot) {
            dot.className = 'dot ' +
                (state === 'connected' ? 'ok' :
                 state === 'error' ? 'bad' : '');
        }

        if (labelElement) {
            labelElement.textContent = label;
        }
    });
}

function subscribePrivateChannel(channelName, handlers = {}) {
    if (!socket) return null;

    const fullName = 'private-' + channelName;

    let channel = subscribedChannels.get(fullName);

    if (!channel) {
        channel = socket.subscribe(fullName);
        subscribedChannels.set(fullName, channel);

        channel.bind('pusher:subscription_succeeded', () => {
            console.log('Subscribed:', fullName);
        });

        channel.bind('pusher:subscription_error', error => {
            console.error('Subscription failed:', fullName, error);
        });
    }

    if (handlers.onCreated) {
        channel.unbind(
            window.APP_CONFIG.ORDER_CREATED_EVENT
        );

        channel.bind(
            window.APP_CONFIG.ORDER_CREATED_EVENT,
            handlers.onCreated
        );
    }

    if (handlers.onStatus) {
        channel.unbind(
            window.APP_CONFIG.ORDER_STATUS_EVENT
        );

        channel.bind(
            window.APP_CONFIG.ORDER_STATUS_EVENT,
            handlers.onStatus
        );
    }

    return channel;
}

function connectRealtime({ onCreated = null, onStatus = null } = {}) {
    const config = window.APP_CONFIG;

    if (!config?.SOKETI_ENABLED) {
        socketState('wait', 'Soketi غير مفعّل');
        return null;
    }

    if (typeof Pusher === 'undefined') {
        socketState('error', 'مكتبة Pusher غير محمّلة');
        return null;
    }

    if (!token()) {
        socketState('wait', 'يرجى تسجيل الدخول');
        return null;
    }

    if (socket) {
        return socket;
    }

    socket = new Pusher(config.SOKETI_KEY, {
        cluster: 'mt1',

        wsHost: config.SOKETI_HOST,
        wsPort: Number(config.SOKETI_PORT),
        wssPort: Number(config.SOKETI_PORT),

        forceTLS: config.SOKETI_FORCE_TLS,

        enabledTransports: config.SOKETI_FORCE_TLS
            ? ['wss']
            : ['ws'],

        disableStats: true,

        channelAuthorization: {
            endpoint: config.BROADCAST_AUTH_ENDPOINT,
            transport: 'ajax',
            headers: {
                Authorization: 'Bearer ' + token(),
                Accept: 'application/json'
            }
        }
    });

    socket.connection.bind('connected', () => {
        socketState('connected', 'متصل بـ Soketi');
        console.log('Soketi connected');
    });

    socket.connection.bind('disconnected', () => {
        socketState('wait', 'انقطع الاتصال');
    });

    socket.connection.bind('error', error => {
        socketState('error', 'خطأ في اتصال Soketi');
        console.error(error);
    });

    const user = currentUser();

    if (user?.role === 'admin') {
        subscribePrivateChannel(
            config.ADMIN_ORDERS_CHANNEL,
            { onCreated, onStatus }
        );
    }

    return socket;
}

function subscribeToCustomerOrders(orders, onStatus) {
    if (!socket || !Array.isArray(orders)) return;

    orders.forEach(order => {
        if (!order.id) return;

        subscribePrivateChannel(
            window.APP_CONFIG.CUSTOMER_ORDER_CHANNEL_PREFIX + order.id,
            { onStatus }
        );
    });
}

function disconnectRealtime() {
    if (socket) {
        socket.disconnect();
        socket = null;
    }

    subscribedChannels.clear();
    socketState('wait', 'غير متصل');
}
