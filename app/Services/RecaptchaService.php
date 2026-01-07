<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verify the reCAPTCHA token
     */
    public function verify(string $token, ?float $threshold = null): bool
    {
        if (!config('recaptcha.secret_key')) {
            return true;
        }

        $threshold = $threshold ?? (float) config('recaptcha.threshold', 0.3);
        $failOpen = (bool) config('recaptcha.fail_open', false);
        $version = (string) config('recaptcha.version');

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('recaptcha.secret_key'),
                'response' => $token,
            ]);

            $data = $response->json();

            if (!isset($data['success']) || !$data['success']) {
                Log::warning('reCAPTCHA verification failed (success=false)', [
                    'http_status' => $response->status(),
                    'error_codes' => $data['error-codes'] ?? null,
                    'hostname' => $data['hostname'] ?? null,
                    'action' => $data['action'] ?? null,
                    'score' => $data['score'] ?? null,
                    'version' => $version,
                    'threshold' => $threshold,
                    'fail_open' => $failOpen,
                ]);
                return $failOpen;
            }

            // For v3, check the score
            if ($version === 'v3') {
                $score = $data['score'] ?? null;
                $passed = is_numeric($score) && ((float) $score) >= $threshold;

                if (!$passed) {
                    Log::warning('reCAPTCHA verification failed (score below threshold)', [
                        'http_status' => $response->status(),
                        'hostname' => $data['hostname'] ?? null,
                        'action' => $data['action'] ?? null,
                        'score' => $score,
                        'version' => $version,
                        'threshold' => $threshold,
                        'fail_open' => $failOpen,
                    ]);
                }

                return $passed;
            }

            return true;
        } catch (\Exception $e) {
            Log::warning('reCAPTCHA verification exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'version' => $version,
                'threshold' => $threshold,
                'fail_open' => $failOpen,
            ]);
            return $failOpen;
        }
    }
}
