<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * يعرض EXPLAIN (ANALYZE, BUFFERS) للاستعلامات الأبطأ في النظام.
 *   php artisan perf:explain            ملخص (نوع المسح + الزمن)
 *   php artisan perf:explain --full     الخطة كاملة
 * شغّله قبل الفهارس وبعدها وقارن (Seq Scan => Index Scan).
 */
class PerfExplain extends Command
{
    protected $signature = 'perf:explain {--full : print the full plan}';
    protected $description = 'EXPLAIN ANALYZE for the heaviest queries (use before/after adding indexes)';

    public function handle(): int
    {
        $userId = (int) DB::table('orders')->orderBy('user_id')->value('user_id');
        $orderIds = DB::table('orders')->where('user_id', $userId)->limit(20)->pluck('id')->all();
        $in = implode(',', array_map('intval', $orderIds ?: [0]));

        $queries = [
            '1. قائمة المنتجات (الصفحة الأولى)' =>
                'SELECT * FROM products ORDER BY created_at DESC, id DESC LIMIT 20',
            '2. قائمة المنتجات + تصنيف' =>
                "SELECT * FROM products WHERE category = 'phones' ORDER BY created_at DESC, id DESC LIMIT 20",
            '3. البحث بالاسم' =>
                "SELECT * FROM products WHERE name ILIKE '%phone 12%' ORDER BY created_at DESC, id DESC LIMIT 20",
            '4. طلباتي (مستخدم واحد)' =>
                "SELECT * FROM orders WHERE user_id = {$userId} ORDER BY created_at DESC",
            '5. طلبات الإدارة حسب الحالة' =>
                "SELECT * FROM orders WHERE status = 'pending' ORDER BY created_at DESC LIMIT 20",
            '6. أصناف مجموعة طلبات (eager loading)' =>
                "SELECT * FROM order_items WHERE order_id IN ({$in})",
            '7. عدّ الصفحات (paginate count)' =>
                "SELECT count(*) FROM orders WHERE status = 'pending'",
        ];

        foreach ($queries as $title => $sql) {
            $this->newLine();
            $this->info($title);
            $this->line("  <fg=gray>{$sql}</>");

            // نستخدم الأساسية دائماً حتى نقيس نفس الشيء
            $plan = collect(DB::connection()->select('EXPLAIN (ANALYZE, BUFFERS) ' . $sql))
                ->map(fn ($r) => (string) ((array) $r)['QUERY PLAN'])
                ->all();

            if ($this->option('full')) {
                foreach ($plan as $line) {
                    $this->line('  ' . $line);
                }
                continue;
            }

            $scan = collect($plan)->first(fn ($l) => preg_match('/(Seq Scan|Index Scan|Index Only Scan|Bitmap Heap Scan|Bitmap Index Scan)/', $l));
            $exec = collect($plan)->first(fn ($l) => str_contains($l, 'Execution Time'));
            $this->line('  scan: ' . trim(preg_replace('/\s+\(cost.*$/', '', (string) $scan)));
            $this->line('  ' . trim((string) $exec));
        }

        return self::SUCCESS;
    }
}
