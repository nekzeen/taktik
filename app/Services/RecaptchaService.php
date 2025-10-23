<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    /**
     * Verify the reCAPTCHA token
     */
    public function verify(string $token, float $threshold = 0.5): bool
    {
        if (!config('recaptcha.secret_key')) {
            return true;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('recaptcha.secret_key'),
                'response' => $token,
            ]);

            $data = $response->json();

            if (!isset($data['success']) || !$data['success']) {
                return false;
            }

            // For v3, check the score
            if (config('recaptcha.version') === 'v3') {
                return isset($data['score']) && $data['score'] >= $threshold;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
