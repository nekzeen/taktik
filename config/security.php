<?php

return [
    'alert_email' => env('SECURITY_ALERT_EMAIL'),

    'headers' => [
        'csp' => env('SECURITY_CSP', "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; img-src 'self' data: https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; connect-src 'self' https:; font-src 'self' data: https:; object-src 'none';"),
        'hsts' => [
            'enabled' => (bool) env('SECURITY_HSTS_ENABLED', true),
            'max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
            'include_subdomains' => (bool) env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
            'preload' => (bool) env('SECURITY_HSTS_PRELOAD', false),
        ],
    ],

    'rate_limits' => [
        'login' => [
            'per_minute' => (int) env('SECURITY_LOGIN_PER_MINUTE', 10),
        ],
        'auth' => [
            'per_minute' => (int) env('SECURITY_AUTH_PER_MINUTE', 30),
        ],
        'match_api' => [
            'per_minute' => (int) env('SECURITY_MATCH_API_PER_MINUTE', 120),
        ],
        'spectator_polling' => [
            'per_minute' => (int) env('SECURITY_SPECTATOR_POLL_PER_MINUTE', 60),
        ],
    ],

    'alert_throttling' => [
        'enabled' => (bool) env('SECURITY_ALERT_THROTTLE_ENABLED', true),
        'failed_login_threshold' => (int) env('SECURITY_FAILED_LOGIN_THRESHOLD', 5),
        'cooldown_seconds' => (int) env('SECURITY_FAILED_LOGIN_COOLDOWN_SECONDS', 900),
        'window_seconds' => (int) env('SECURITY_FAILED_LOGIN_WINDOW_SECONDS', 900),
    ],

    'weekly_report' => [
        'enabled' => (bool) env('SECURITY_WEEKLY_REPORT_ENABLED', true),
        'email' => env('SECURITY_WEEKLY_REPORT_EMAIL', env('SECURITY_ALERT_EMAIL')),
    ],
];
