<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * ضمان "اقرأ ما كتبته" (Read-your-writes) عند استخدام نسخة القراءة.
 *
 * المشكلة: النسخة (Replica) تتأخر عن الأساسية بأجزاء من الثانية. لو أنشأ الزبون
 * طلباً ثم طلب قائمة طلباته فوراً، وذهبت القراءة للنسخة، قد لا يرى طلبه.
 *
 * الحل: بعد أي كتابة ناجحة نسجّل في Redis علامة قصيرة العمر (replica_lag_window ثوانٍ).
 * أثناء وجود العلامة تُقرأ بيانات هذا العميل من القاعدة الأساسية.
 *  - علامة لكل عميل (مفتاحها هاش التوكن) => كل مستخدم يرى كتاباته فوراً.
 *  - علامة عامة للمنتجات => بعد تعديل منتج لا نملأ Redis ببيانات قديمة من النسخة.
 * إذا تعطّل Redis نختار الأمان: القراءة من الأساسية.
 */
class ReadYourWrites
{
    private const PRODUCTS_KEY = 'ryw:products';

    public static function enabled(): bool
    {
        return (bool) config('performance.read_replica_enabled');
    }

    private static function window(): int
    {
        return max(1, (int) config('performance.replica_lag_window', 5));
    }

    /** مفتاح العميل = هاش التوكن (بدون أي استعلام قاعدة بيانات). null إن لم يوجد توكن. */
    public static function clientKey(Request $request): ?string
    {
        $token = $request->bearerToken();

        return $token ? 'ryw:client:' . sha1($token) : null;
    }

    public static function markKey(?string $key): void
    {
        if (!$key) {
            return;
        }
        try {
            Cache::store('redis')->put($key, 1, self::window());
        } catch (Throwable) {
            // لا نكسر الطلب بسبب Redis
        }
    }

    public static function markToken(string $plainToken): void
    {
        self::markKey('ryw:client:' . sha1($plainToken));
    }

    public static function hasRecentWrite(?string $key): bool
    {
        if (!$key) {
            return false;
        }
        try {
            return Cache::store('redis')->has($key);
        } catch (Throwable) {
            return true; // fail-safe: الأساسية
        }
    }

    public static function markProductsWrite(): void
    {
        self::markKey(self::PRODUCTS_KEY);
    }

    /** هل جرى تعديل على المنتجات مؤخراً (من أي مستخدم)؟ */
    public static function productsRecentlyWritten(): bool
    {
        if (!self::enabled()) {
            return false;
        }

        return self::hasRecentWrite(self::PRODUCTS_KEY);
    }
}
