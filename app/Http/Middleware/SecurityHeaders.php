<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser security headers for every page.
 *
 * Code, styles and fonts load only from this site.
 * Alpine.js needs 'unsafe-eval'.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // While `npm run dev` is running, styles and scripts come from the Vite dev server.
        $vite = Vite::isRunningHot() ? self::origin(trim((string) @file_get_contents(Vite::hotFile()))) : null;
        $dev = $vite ? ' '.$vite.' '.preg_replace('#^http#', 'ws', $vite) : '';

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'".$dev,
            "style-src 'self' 'unsafe-inline'".$dev,
            "img-src 'self' data: blob:",
            "font-src 'self' data:".$dev,
            "connect-src 'self'".$dev,
            "frame-src 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $headers = [
            'Content-Security-Policy' => $csp,
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    /** "https://example.com:8443/path" -> "https://example.com:8443" */
    private static function origin(?string $url): ?string
    {
        $parts = $url ? parse_url($url) : false;
        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }
}
