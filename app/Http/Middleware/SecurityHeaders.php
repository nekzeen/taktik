<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        $csp = (string) config('security.headers.csp');
        if ($csp !== '') {
            if (!$request->is('api') && !$request->is('api/*')) {
                $csp = $this->ensureScriptSrcContainsUnsafeEval($csp);
            }
            $response->headers->set('Content-Security-Policy', $csp);
        }

        $shouldHsts = (bool) config('security.headers.hsts.enabled', true)
            && $request->isSecure()
            && config('app.env') === 'production';

        if ($shouldHsts) {
            $maxAge = (int) config('security.headers.hsts.max_age', 31536000);
            $includeSubDomains = (bool) config('security.headers.hsts.include_subdomains', true);

            $value = 'max-age='.$maxAge;
            if ($includeSubDomains) {
                $value .= '; includeSubDomains';
            }

            if ((bool) config('security.headers.hsts.preload', false)) {
                $value .= '; preload';
            }

            $response->headers->set('Strict-Transport-Security', $value);
        }

        return $response;
    }

    private function ensureScriptSrcContainsUnsafeEval(string $csp): string
    {
        if (stripos($csp, "'unsafe-eval'") !== false) {
            return $csp;
        }

        if (!preg_match('/(^|;\s*)script-src\s+([^;]*)(;|$)/i', $csp, $m)) {
            $suffix = rtrim($csp);
            if ($suffix !== '' && !str_ends_with($suffix, ';')) {
                $suffix .= ';';
            }
            return $suffix . " script-src 'self' 'unsafe-inline' 'unsafe-eval' https:;";
        }

        $full = $m[0];
        $sources = trim($m[2]);

        $replacement = str_replace($full, str_replace($sources, trim($sources . " 'unsafe-eval'"), $full), $csp);

        return $replacement;
    }
}
