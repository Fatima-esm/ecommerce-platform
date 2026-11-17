
window.APP_CONFIG = {

    // Laravel API
    API_BASE_URL: 'http://127.0.0.1:8088',

    // Soketi
    SOKETI_ENABLED: true,
    SOKETI_KEY: 'ecommerce-key',
    SOKETI_HOST: '127.0.0.1',
    SOKETI_PORT: 6001,
    SOKETI_FORCE_TLS: false,

    // Private Channels
    CUSTOMER_ORDER_CHANNEL_PREFIX: 'orders.',
    ADMIN_ORDERS_CHANNEL: 'admin.orders',

    // Laravel broadcastAs() event names
    ORDER_CREATED_EVENT: 'order.created',
    ORDER_STATUS_EVENT: 'order.status.changed',

    // Sanctum authentication endpoint
    BROADCAST_AUTH_ENDPOINT:
        'http://127.0.0.1:8088/api/broadcasting/auth'
};
