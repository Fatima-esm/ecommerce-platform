<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| فهارس المتطلب الثالث (نتيجة تحليل EXPLAIN)
|--------------------------------------------------------------------------
| كل فهرس هنا مرتبط باستعلام فعلي في الواجهات:
|
| products_created_id_idx           قائمة المنتجات:  ORDER BY created_at DESC, id DESC LIMIT 20
| products_category_created_id_idx  القائمة مع تصنيف: WHERE category = ? ORDER BY created_at DESC
| products_name_trgm_idx            البحث بالاسم:   WHERE name ILIKE '%...%' (btree لا يفيد مع % في البداية)
| orders_user_created_idx           "طلباتي":       WHERE user_id = ? ORDER BY created_at DESC
| orders_status_created_idx         طلبات الإدارة:  WHERE status = ? ORDER BY created_at DESC
| order_items_order_id_idx          تحميل أصناف الطلب (PostgreSQL لا ينشئ فهرساً تلقائياً للمفتاح الأجنبي)
| order_items_product_id_idx        ربط الأصناف بالمنتجات
|
| الثمن: كل فهرس يبطئ INSERT/UPDATE قليلاً ويأخذ مساحة (انظر README: قياس كلفة الكتابة).
| للقياس "قبل": php artisan migrate:rollback --step=1   ثم   php artisan migrate للقياس "بعد".
*/

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        DB::statement('CREATE INDEX IF NOT EXISTS products_created_id_idx ON products (created_at DESC, id DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS products_category_created_id_idx ON products (category, created_at DESC, id DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS products_name_trgm_idx ON products USING gin (name gin_trgm_ops)');

        DB::statement('CREATE INDEX IF NOT EXISTS orders_user_created_idx ON orders (user_id, created_at DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS orders_status_created_idx ON orders (status, created_at DESC)');

        DB::statement('CREATE INDEX IF NOT EXISTS order_items_order_id_idx ON order_items (order_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS order_items_product_id_idx ON order_items (product_id)');

        DB::statement('ANALYZE products');
        DB::statement('ANALYZE orders');
        DB::statement('ANALYZE order_items');
    }

    public function down(): void
    {
        foreach ([
            'products_created_id_idx',
            'products_category_created_id_idx',
            'products_name_trgm_idx',
            'orders_user_created_idx',
            'orders_status_created_idx',
            'order_items_order_id_idx',
            'order_items_product_id_idx',
        ] as $index) {
            DB::statement("DROP INDEX IF EXISTS {$index}");
        }
    }
};
