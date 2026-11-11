<?php

namespace App\Http\Middleware;

use App\Support\ReadYourWrites;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReadYourWritesMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!ReadYourWrites::enabled()) {
            return $next($request);
        }

        $key = ReadYourWrites::clientKey($request);

        // كتب هذا العميل قبل لحظات؟ اقرأ من الأساسية في هذا الطلب (حتى مصادقة التوكن نفسها).
        if (ReadYourWrites::hasRecentWrite($key)) {
            DB::connection()->useWriteConnectionWhenReading();
            $request->attributes->set('db_read_source', 'primary');
        }

        $response = $next($request);

        // طلب كتابة ناجح => سجّل العلامة لهذا العميل
        if (!$request->isMethodSafe() && $response->isSuccessful()) {
            ReadYourWrites::markKey($key);
        }

        return $response;
    }
}
