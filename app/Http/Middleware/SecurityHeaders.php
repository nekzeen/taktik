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
}
