
## الصفحات
- `pages/auth.html`: تسجيل/دخول.
- `pages/products.html`: عرض المنتجات.
- `pages/product.html`: تفاصيل المنتج والمخزون.
- `pages/checkout.html`: إنشاء طلب.
- `pages/my-orders.html`: طلبات العميل.
- `pages/admin.html`: إضافة منتج، عرض الطلبات، وتغيير الحالة.

## المسارات المأخوذة من Postman
POST `/api/v1/auth/register`; POST `/api/v1/auth/login`; GET `/api/v1/products`; GET `/api/v1/products/{id}`; POST `/api/v1/orders`; GET `/api/v1/orders`; GET `/api/v1/admin/orders`; POST `/api/v1/admin/products`; PATCH `/api/v1/admin/orders/{id}/status`.

## Soketi
إعدادات Soketi والقنوات والأحداث في `assets/config.js`. الاتصال معطل افتراضياً حتى تضع القيم الصحيحة. أسماء القنوات والأحداث غير موجودة في Postman Collection؛ طابقها مع `routes/channels.php` وكلاسات Events في Laravel. يستخدم المثال Pusher JS مباشرة للتوضيح مع `/broadcasting/auth` وBearer token. إذا كان مشروعك يستخدم إعداداً مختلفاً لـ Laravel Echo/Soketi فعدّل `assets/realtime.js` وفق التنفيذ الفعلي.

الواجهة تعرض حالة الاتصال، لكن لا تعتبر إخفاء الواجهة بديلاً عن Authorization في Laravel. استخدم Postman لاختبار رفض الاشتراكات غير المصرح بها. هذا نموذج عرض/تجربة وليس إعداداً إنتاجياً.
