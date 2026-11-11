<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * رؤوس تشخيصية تساعد في شرح القياسات أثناء المقابلة (تُفعَّل بـ PERF_DEBUG_HEADERS=true):
 *   X-DB-Queries       عدد استعلامات القاعدة في هذا الطلب (لكشف N+1)
 *   X-DB-Served-By     كم استعلاماً خدمته النسخة وكم خدمته الأساسية
 *   X-Cache            HIT/MISS لـ Redis (تضعه الـ Controller)
 *   X-Response-Time-Ms زمن المعالجة داخل التطبيق
 */
class PerfHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('performance.debug_headers')) {
            return $next($request);
        }

        $start = microtime(true);
        $reads = 0;
        $writes = 0;

        DB::listen(function (QueryExecuted $q) use (&$reads, &$writes) {
            if (($q->readWriteType ?? 'write') === 'read') {
                $reads++;
            } else {
                $writes++;
            }
        });

        $response = $next($request);

        $total = $reads + $writes;
        // القراءات تخدمها النسخة فقط عندما تكون مفعّلة ولم يُجبر الطلب على الأساسية
        $replica = (config('performance.read_replica_enabled') && !$request->attributes->has('db_read_source')) ? $reads : 0;

        $response->headers->set('X-DB-Queries', (string) $total);
        $response->headers->set('X-DB-Served-By', sprintf('replica=%d,primary=%d', $replica, $total - $replica));
        $response->headers->set('X-Cache', (string) $request->attributes->get('x_cache', 'NONE'));
        $response->headers->set('X-Response-Time-Ms', (string) round((microtime(true) - $start) * 1000, 1));

        return $response;
    }
}
