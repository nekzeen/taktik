<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class SendEmailVerificationNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        Log::info('SendEmailVerificationNotification triggered for user: ' . $event->user->email);
        
        if (!$event->user->hasVerifiedEmail()) {
            Log::info('Sending verification email to: ' . $event->user->email);
            $event->user->sendEmailVerificationNotification();
            Log::info('Verification email sent to: ' . $event->user->email);
        } else {
            Log::info('User already verified: ' . $event->user->email);
        }
    }
}
