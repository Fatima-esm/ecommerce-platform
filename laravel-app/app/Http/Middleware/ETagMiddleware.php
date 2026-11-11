<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * التخزين المؤقت في HTTP عبر ETag:
 *  1) الخادم يحسب بصمة (ETag) للرد ويرسلها مع الجسم.
 *  2) العميل يعيد الطلب مع If-None-Match: <البصمة>.
 *  3) إن لم يتغير شيء يرد الخادم 304 بدون جسم (توفير الشبكة).
 *
 * الاستخدام في المسارات:  ->middleware('etag:public')  أو  ->middleware('etag:private')
 * (private للبيانات الخاصة بمستخدم، كالطلبات).
 */
class ETagMiddleware
{
    public function handle(Request $request, Closure $next, string $visibility = 'public'): Response
    {
        $response = $next($request);

        if (!config('performance.etag_enabled')
            || !$request->isMethodCacheable()
            || $response->getStatusCode() !== 200) {
            return $response;
        }

        if ($response instanceof BinaryFileResponse) {
            $file = $response->getFile();
            $etag = md5($file->getPathname() . '|' . $file->getMTime() . '|' . $file->getSize());
        } else {
            $content = $response->getContent();
            if ($content === false || $content === '') {
                return $response;
            }
            $etag = md5($content);
        }

        $response->setEtag($etag);
        // no-cache = "خزّن، لكن اسأل الخادم قبل كل استخدام" => يضمن أن التعديل يظهر فوراً
        $response->headers->set('Cache-Control', $visibility . ', no-cache');

        // إذا طابقت If-None-Match يحوّل الرد إلى 304 ويحذف الجسم
        $response->isNotModified($request);

        return $response;
    }
}
