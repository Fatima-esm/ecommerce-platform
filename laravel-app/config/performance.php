<?php

/*
|--------------------------------------------------------------------------
| إعدادات المتطلب الثالث (Performance & Caching)
|--------------------------------------------------------------------------
| كل تحسين له مفتاح تشغيل/إيقاف مستقل، حتى نقيس "قبل" و"بعد" كل تحسين
| لوحده (تغيير شيء واحد في كل مرة). تُضبط من docker-compose.yml.
*/

return [
    // Redis: تخزين مؤقت في الخادم لبيانات المنتجات
    'cache_enabled' => (bool) env('PERF_CACHE', false),

    // ETag: ردّ 304 بدون جسم عندما لا تتغير البيانات
    'etag_enabled' => (bool) env('PERF_ETAG', false),

    // نسخة القراءة (Read Replica) + ضمان "اقرأ ما كتبته"
    'read_replica_enabled' => (bool) env('PERF_READ_REPLICA', false),

    // رؤوس تشخيصية (X-DB-Queries, X-Response-Time-Ms, X-Cache, X-DB-Read)
    'debug_headers' => (bool) env('PERF_DEBUG_HEADERS', false),

    // صلاحية بيانات المنتج المفرد في Redis (ثوانٍ)
    'product_ttl' => (int) env('PERF_PRODUCT_TTL', 600),

    // صلاحية صفحات قائمة المنتجات (ثوانٍ) — أقصر لأنها تتأثر بأي تعديل
    'list_ttl' => (int) env('PERF_LIST_TTL', 60),

    // بعد أي كتابة: نقرأ من القاعدة الأساسية لهذه المدة (ثوانٍ)
    // يجب أن تكون أكبر من أقصى تأخر متوقع للنسخة (replication lag)
    'replica_lag_window' => (int) env('PERF_REPLICA_LAG_WINDOW', 5),
];
