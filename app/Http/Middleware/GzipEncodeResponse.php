<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GzipEncodeResponse
{
    /**
     * Handle an incoming request.
     * Compresses text/HTML/JSON/CSS/JS HTTP responses with gzip for high performance.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (defined('LARAVEL_START')) {
            $boot = defined('LARAVEL_APP_BOOTED') ? round((LARAVEL_APP_BOOTED - LARAVEL_START) * 1000, 1) : 0;
            $appTime = defined('LARAVEL_APP_BOOTED') ? round((microtime(true) - LARAVEL_APP_BOOTED) * 1000, 1) : round((microtime(true) - LARAVEL_START) * 1000, 1);
            $response->headers->set('Server-Timing', "boot;dur={$boot}, app;dur={$appTime}");
        }

        // Only compress safe idempotent GET/HEAD requests
        if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
            return $response;
        }

        // Check if client supports gzip
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (!str_contains($acceptEncoding, 'gzip')) {
            return $response;
        }

        // Avoid re-compressing already encoded responses
        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }

        // Only compress responses with text/html, application/json, etc.
        $contentType = $response->headers->get('Content-Type', '');
        if (!preg_match('/text\/|application\/json|application\/javascript|image\/svg\+xml/i', $contentType)) {
            return $response;
        }

        $content = $response->getContent();
        if (!$content || strlen($content) < 1024) {
            return $response;
        }

        // Compress content with zlib level 5 (optimal balance between CPU and compression ratio)
        $compressed = gzencode($content, 5);
        if ($compressed !== false && strlen($compressed) < strlen($content)) {
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Content-Length', (string)strlen($compressed));
            $response->headers->set('Vary', 'Accept-Encoding', false);
        }

        return $response;
    }
}
