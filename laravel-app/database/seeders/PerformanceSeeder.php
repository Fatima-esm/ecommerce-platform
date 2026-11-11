<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * بيانات وهمية كبيرة لقياس الأداء (المتطلب الثالث).
 *   php artisan db:seed --class=PerformanceSeeder
 * الأحجام تُضبط من المتغيرات: PERF_USERS / PERF_PRODUCTS / PERF_ORDERS
 * (الافتراضي: 5,000 مستخدم، 200,000 منتج، 300,000 طلب، ~900,000 صنف طلب).
 *
 * نستخدم INSERT ... SELECT generate_series داخل PostgreSQL: أسرع بمئات المرات من Eloquent.
 * كل المستخدمين يحملون كلمة المرور:  Password123!   والبريد: user{N}@perf.test
 */
class PerformanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = (int) env('PERF_USERS', 5000);
        $products = (int) env('PERF_PRODUCTS', 200000);
        $orders = (int) env('PERF_ORDERS', 300000);

        $hash = Hash::make('Password123!'); // هاش واحد يُعاد استخدامه (bcrypt بطيء عمداً)

        $this->command?->info("users: {$users}");
        DB::statement("
            INSERT INTO users (name, email, role, password, created_at, updated_at)
            SELECT 'Perf User ' || g, 'user' || g || '@perf.test', 'customer', CAST(? AS text), now(), now()
            FROM generate_series(1, CAST(? AS int)) g
            ON CONFLICT (email) DO NOTHING
        ", [$hash, $users]);

        $this->command?->info("products: {$products}");
        DB::statement("
            INSERT INTO products (name, description, price, category, created_at, updated_at)
            SELECT
                'Product ' || g || ' ' || (ARRAY['Phone','Laptop','Camera','Watch','Speaker','Tablet','Monitor','Mouse'])[1 + g % 8],
                'Description of product number ' || g,
                round((random() * 990 + 10)::numeric, 2),
                (ARRAY['phones','laptops','cameras','wearables','audio','tablets','monitors','accessories'])[1 + g % 8],
                now() - (random() * interval '365 days'),
                now()
            FROM generate_series(1, CAST(? AS int)) g
        ", [$products]);

        [$minUser, $maxUser] = [
            (int) DB::table('users')->where('email', 'like', 'user%@perf.test')->min('id'),
            (int) DB::table('users')->where('email', 'like', 'user%@perf.test')->max('id'),
        ];
        [$minProd, $maxProd] = [(int) DB::table('products')->min('id'), (int) DB::table('products')->max('id')];

        $this->command?->info("orders: {$orders}");
        DB::statement("
            INSERT INTO orders (user_id, order_ref, total_amount, status, created_at, updated_at)
            SELECT
                CAST(? AS int) + floor(random() * (CAST(? AS int) - CAST(? AS int) + 1))::int,
                gen_random_uuid()::text,
                round((random() * 2000 + 10)::numeric, 2),
                (ARRAY['pending','confirmed','confirmed','confirmed','cancelled'])[1 + floor(random() * 5)::int],
                ts, ts
            FROM (SELECT now() - (random() * interval '365 days') AS ts FROM generate_series(1, CAST(? AS int))) t
        ", [$minUser, $maxUser, $minUser, $orders]);

        $this->command?->info('order_items: ~3 per order');
        DB::statement("
            INSERT INTO order_items (order_id, product_id, quantity, price, created_at, updated_at)
            SELECT
                o.id,
                CAST(? AS int) + floor(random() * (CAST(? AS int) - CAST(? AS int) + 1))::int,
                1 + floor(random() * 3)::int,
                round((random() * 990 + 10)::numeric, 2),
                o.created_at, o.created_at
            FROM orders o
            CROSS JOIN generate_series(1, 3)
            WHERE o.order_ref IS NOT NULL
        ", [$minProd, $maxProd, $minProd]);

        DB::statement('ANALYZE users');
        DB::statement('ANALYZE products');
        DB::statement('ANALYZE orders');
        DB::statement('ANALYZE order_items');

        $this->command?->info(sprintf(
            'done. products %d..%d, users %d..%d (password: Password123!)',
            $minProd, $maxProd, $minUser, $maxUser
        ));
        $this->command?->warn('لا تنسَ تعبئة المستودع بكميات لهذه المنتجات (انظر README: قسم تعبئة المستودع).');
    }
}
