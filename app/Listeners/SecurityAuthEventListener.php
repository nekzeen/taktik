<?php

namespace App\Listeners;

use App\Mail\SecurityAlert;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SecurityAuthEventListener
{
    public function handle(object $event): void
    {
        $type = class_basename($event);

        $payload = $this->buildPayload($event);

        Log::channel('security')->info($type, $payload);

        $to = config('security.alert_email');
        if (!$to) {
            return;
        }

        if ($event instanceof Failed) {
            if (!$this->shouldSendFailedLoginAlert($payload)) {
                return;
            }

            Mail::to($to)->send(new SecurityAlert(
                subject: 'Alerte sécurité : tentative de connexion échouée',
                type: $type,
                payload: $payload,
            ));
        }
    }

    private function shouldSendFailedLoginAlert(array $payload): bool
    {
        $enabled = (bool) config('security.alert_throttling.enabled', true);
        if (!$enabled) {
            return true;
        }

        $threshold = (int) config('security.alert_throttling.failed_login_threshold', 5);
        $windowSeconds = (int) config('security.alert_throttling.window_seconds', 900);
        $cooldownSeconds = (int) config('security.alert_throttling.cooldown_seconds', 900);

        $identifier = (string) ($payload['credentials_email'] ?? $payload['email'] ?? 'unknown');
        $keyBase = 'security_alert_failed_login:'.hash('sha256', $identifier);
        $cooldownKey = $keyBase.':cooldown';
        $countKey = $keyBase.':count';

        if (Cache::has($cooldownKey)) {
            return false;
        }

        $count = (int) Cache::get($countKey, 0);
        $count++;
        Cache::put($countKey, $count, $windowSeconds);

        if ($count < $threshold) {
            return false;
        }

        Cache::put($cooldownKey, true, $cooldownSeconds);
        Cache::forget($countKey);

        return true;
    }

    private function buildPayload(object $event): array
    {
        if ($event instanceof Login) {
            return [
                'user_id' => $event->user?->id,
                'email' => $event->user?->email,
                'guard' => $event->guard,
                'remember' => $event->remember,
            ];
        }

        if ($event instanceof Failed) {
            return [
                'user_id' => $event->user?->id,
                'email' => $event->user?->email,
                'guard' => $event->guard,
                'credentials_email' => $event->credentials['email'] ?? null,
            ];
        }

        if ($event instanceof Logout) {
            return [
                'user_id' => $event->user?->id,
                'email' => $event->user?->email,
                'guard' => $event->guard,
            ];
        }

        if ($event instanceof PasswordReset) {
            return [
                'user_id' => $event->user?->id,
                'email' => $event->user?->email,
            ];
        }

        return [];
    }
}
