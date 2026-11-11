<?php

namespace App\Http\Middleware;

use App\Support\ApiErrors;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            // نفس صيغة الأخطاء الموحدة في كل الواجهات
            return ApiErrors::json(403, 'FORBIDDEN', 'Admin privileges are required.');
        }

        return $next($request);
    }
}
