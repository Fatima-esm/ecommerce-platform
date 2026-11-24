const API = (
    window.APP_CONFIG?.API_BASE_URL ||
    window.location.origin
).replace(/\/+$/, '');

const TK = 'token';
const UK = 'user';
const CART_KEY = 'shopapp_cart';

function token() {
    return sessionStorage.getItem(TK) || '';
}

function currentUser() {
    try {
        return JSON.parse(sessionStorage.getItem(UK) || 'null');
    } catch {
        return null;
    }
}

function saveSession(data) {
    const response = data?.data ?? data;

    if (response?.token) {
        sessionStorage.setItem(TK, response.token);
    }

    if (response?.user) {
        sessionStorage.setItem(
            UK,
            JSON.stringify(response.user)
        );
    }
}

// تسجيل الخروج من التبويب الحالي فقط
function logout() {
    sessionStorage.removeItem(TK);
    sessionStorage.removeItem(UK);

    location.href = 'auth.html';
}

// ==========================================
// 3. الاتصال بواجهة Laravel API
// ==========================================

async function api(path, options = {}) {
    const headers = {
        Accept: 'application/json',
        ...(options.headers || {})
    };

    if (token()) {
        headers.Authorization = 'Bearer ' + token();
    }

    const response = await fetch(API + path, {
        ...options,
        headers
    });

    if (response.status === 204) {
        return {};
    }

    const text = await response.text();

    let data = {};

    try {
        data = text ? JSON.parse(text) : {};
    } catch {
        data = {
            message: text || 'استجابة غير صالحة من الخادم'
        };
    }

    if (!response.ok) {
        const apiError = (data && typeof data.error === 'object' && data.error !== null)
            ? data.error
            : null;

        let message =
            apiError?.message ||
            data.message ||
            (typeof data.error === 'string' ? data.error : '') ||
            `HTTP ${response.status}`;

        if (data.errors && typeof data.errors === 'object') {
            const validationMessages = Object.values(data.errors)
                .flat()
                .filter(Boolean);

            if (validationMessages.length) {
                message = validationMessages.join('\n');
            }
        }

        const err = new Error(message);
        err.status = Number(apiError?.status || data.status || response.status);
        err.code = apiError?.code || data.code || '';
        err.details = apiError?.details ?? data.details ?? null;
        err.data = data;
        throw err;
    }

    return data;
}

// ==========================================
// 4. معالجة أشكال استجابة API
// ==========================================

function extractRows(response) {
    let value = response?.data ?? response;

    if (Array.isArray(value)) {
        return value;
    }

    if (Array.isArray(value?.data)) {
        return value.data;
    }

    return [];
}

function extractObject(response) {
    let value = response?.data ?? response;

    if (
        value &&
        typeof value === 'object' &&
        !Array.isArray(value) &&
        value.data &&
        !Array.isArray(value.data)
    ) {
        value = value.data;
    }

    return value;
}

// ==========================================
// 5. الرسائل والتنسيقات المساعدة
// ==========================================

function msg(element, message, error = false) {
    if (!element) return;

    element.textContent = message;

    element.className = 'notice' + (
        error ? ' error' : ''
    );

    element.classList.remove('hidden');
}

function money(value) {
    const amount = Number(value || 0);

    return amount.toFixed(2) + ' $';
}

function esc(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    })[char]);
}

function badge(status) {
    const allowed = [
        'pending',
        'confirmed',
        'cancelled'
    ];

    const safeStatus = allowed.includes(status)
        ? status
        : 'pending';

    return `
        <span class="badge ${safeStatus}">
            ${safeStatus}
        </span>
    `;
}

// ==========================================
// 6. التحقق من الصلاحيات
// ==========================================

function requireLogin() {
    if (!token()) {
        location.href = 'auth.html';
        return false;
    }

    return true;
}

function requireAdmin() {
    if (!requireLogin()) {
        return false;
    }

    if (currentUser()?.role !== 'admin') {
        alert('هذه الصفحة للمسؤول فقط');
        location.href = 'products.html';
        return false;
    }

    return true;
}

// ==========================================
// 7. إدارة سلة المشتريات
// ==========================================

function getCart() {
    try {
        const cart = JSON.parse(
            localStorage.getItem(CART_KEY) || '[]'
        );

        return Array.isArray(cart) ? cart : [];
    } catch {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(
        CART_KEY,
        JSON.stringify(cart)
    );
}

function normalizeMaxStock(source) {
    const raw = source?.maxStock ?? source?.stock ?? source?.stockQuantity;
    if (raw === null || raw === undefined || raw === '') return null;
    const num = Number(raw);
    return Number.isFinite(num) && num >= 0 ? Math.floor(num) : null;
}

// إضافة منتج إلى السلة مع احترام الحد الأقصى للمخزون
function addToCart(product, quantity = 1) {
    if (!product || !Number.isInteger(Number(product.id)) ||
        Number(product.id) <= 0) {
        throw new Error('بيانات المنتج غير صحيحة');
    }

    const cart = getCart();
    const productId = Number(product.id);
    const qty = Math.max(1, Math.floor(Number(quantity) || 1));
    const maxStock = normalizeMaxStock(product);

    const existing = cart.find(item =>
        Number(item.product_id) === productId
    );

    if (existing) {
        const effectiveMax = maxStock !== null ? maxStock : normalizeMaxStock(existing);
        let nextQty = Number(existing.quantity || 0) + qty;
        if (effectiveMax !== null) {
            existing.maxStock = effectiveMax;
            existing.stock = effectiveMax;
            nextQty = Math.min(nextQty, Math.max(1, effectiveMax));
        }
        existing.quantity = nextQty;
    } else {
        const initialQty = maxStock !== null && maxStock > 0
            ? Math.min(qty, maxStock)
            : qty;

        cart.push({
            product_id: productId,
            quantity: initialQty,
            name: product.name || '',
            price: Number(product.price || 0),
            image_url: product.image_url || null,
            stock: maxStock,
            maxStock: maxStock
        });
    }

    saveCart(cart);
}

// تعديل كمية منتج مع منع تجاوز المخزون المتوفر
function updateCartQuantity(productId, quantity) {
    const cart = getCart();

    const item = cart.find(row =>
        Number(row.product_id) === Number(productId)
    );

    if (!item) return;

    let nextQty = Math.max(1, Math.floor(Number(quantity) || 1));
    const maxStock = normalizeMaxStock(item);

    if (maxStock !== null && maxStock > 0 && nextQty > maxStock) {
        nextQty = maxStock;
    }

    item.quantity = nextQty;
    saveCart(cart);
}

function removeFromCart(productId) {
    const cart = getCart().filter(item =>
        Number(item.product_id) !== Number(productId)
    );

    saveCart(cart);
}

function clearCart() {
    localStorage.removeItem(CART_KEY);
}