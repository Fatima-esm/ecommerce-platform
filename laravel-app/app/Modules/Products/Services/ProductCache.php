<?php

namespace App\Modules\Products\Services;

use App\Support\ReadYourWrites;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * التخزين المؤقت في Redis لبيانات المنتجات.
 *
 * ما الذي نخزّنه؟   بيانات المنتج نفسها (الاسم، السعر، الوصف...) وصفحات القائمة.
 * ما الذي لا نخزّنه؟ الكمية المتوفرة/المحجوزة (تُجلب حيّة من خدمة المستودع دائماً)،
 *                    وكذلك الطلبات والإشعارات (خاصة بكل مستخدم وتتغير باستمرار).
 *
 * سياسة الصلاحية:
 *  - منتج مفرد:  TTL طويل نسبياً (product_ttl) + حذف المفتاح عند التعديل/الحذف.
 *  - صفحات القائمة: TTL قصير (list_ttl) + "رقم إصدار" يُزاد عند أي تعديل،
 *    فتصبح كل الصفحات القديمة غير قابلة للوصول فوراً (وتنتهي وحدها بالـ TTL).
 *
 * الحماية:
 *  - Cache stampede: قفل لكل مفتاح كي لا يعيد 50 طلباً بناء نفس الصفحة معاً.
 *  - Redis معطّل؟ نكمل من قاعدة البيانات (التخزين المؤقت تحسين وليس شرطاً).
 *  - TTL فيه عشوائية بسيطة (jitter) كي لا تنتهي المفاتيح كلها في نفس اللحظة.
 */
class ProductCache
{
    private const VERSION_KEY = 'products:list_version';

    public function enabled(): bool
    {
        return (bool) config('performance.cache_enabled');
    }

    // ---------------------------------------------------------------- القراءة

    public function detailKey(int $id): string
    {
        return "products:detail:{$id}";
    }

    public function listKey(array $filters, int $page): string
    {
        $category = trim((string) ($filters['category'] ?? ''));
        $search = mb_strtolower(trim((string) ($filters['search'] ?? '')));

        return sprintf(
            'products:list:v%d:c=%s:s=%s:p=%d',
            $this->listVersion(),
            $category === '' ? '-' : md5($category),
            $search === '' ? '-' : md5($search),
            max(1, $page)
        );
    }

    /**
     * يعيد القيمة من Redis أو يبنيها عبر $build ثم يخزنها.
     * @param Closure():mixed $build يعيد null إذا لم تُوجد البيانات (لا نخزّن null)
     */
    public function remember(string $key, int $ttl, Closure $build, ?string &$state = null): mixed
    {
        if (!$this->enabled()) {
            $state = 'BYPASS';

            return $build();
        }

        $store = Cache::store('redis');

        try {
            $hit = $store->get($key);
        } catch (Throwable $e) {
            return $this->degrade($e, $build, $state);
        }

        if ($hit !== null) {
            $state = 'HIT';

            return $hit;
        }

        $state = 'MISS';
        $lock = null;
        $locked = false;
        try {
            $lock = $store->lock('lock:' . $key, 10);
            $locked = $lock->block(3);
        } catch (LockTimeoutException) {
            $locked = false;
        } catch (Throwable) {
            $locked = false;
        }

        try {
            if ($locked) {
                // ربما بناها طلب آخر أثناء انتظارنا للقفل
                try {
                    $hit = $store->get($key);
                    if ($hit !== null) {
                        $state = 'HIT';

                        return $hit;
                    }
                } catch (Throwable) {
                }
            }

            $value = $build();

            if ($value !== null) {
                try {
                    $store->put($key, $value, $this->withJitter($ttl));
                } catch (Throwable $e) {
                    Log::warning('product cache put failed', ['error' => $e->getMessage()]);
                }
            }

            return $value;
        } finally {
            if ($locked && $lock) {
                try {
                    $lock->release();
                } catch (Throwable) {
                }
            }
        }
    }

    // ---------------------------------------------------------------- الإبطال

    /**
     * يُستدعى بعد أي كتابة على المنتجات (بعد نجاح التعديل في القاعدة).
     * الترتيب مهم: نسجّل علامة "كتابة حديثة" أولاً (حتى لا تُملأ الذاكرة من نسخة متأخرة)،
     * ثم نبطل المخزن.
     */
    public function afterWrite(?int $productId = null): void
    {
        ReadYourWrites::markProductsWrite();

        if (!$this->enabled()) {
            return;
        }

        try {
            $store = Cache::store('redis');
            if ($productId !== null) {
                $store->forget($this->detailKey($productId));
            }
            $store->increment(self::VERSION_KEY); // يبطل كل صفحات القائمة دفعة واحدة
        } catch (Throwable $e) {
            Log::warning('product cache invalidation failed', ['error' => $e->getMessage()]);
        }
    }

    // ---------------------------------------------------------------- داخلي

    private function listVersion(): int
    {
        try {
            // الافتراضي 0 (وليس 1): INCR على مفتاح غير موجود يعيد 1، فلو كان الافتراضي 1
            // لبقي الإصدار 1 بعد أول تعديل ولما انبطلت الصفحات المخزّنة.
            return (int) Cache::store('redis')->get(self::VERSION_KEY, 0);
        } catch (Throwable) {
            return 0;
        }
    }

    private function withJitter(int $ttl): int
    {
        return (int) round($ttl * (0.9 + mt_rand(0, 20) / 100));
    }

    private function degrade(Throwable $e, Closure $build, ?string &$state): mixed
    {
        Log::warning('redis unavailable, falling back to database', ['error' => $e->getMessage()]);
        $state = 'DOWN';

        return $build();
    }
}
