<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use App\Notifications\NewPlayerRegistered;
use Illuminate\Support\Facades\Notification;

class AssignPlayerRoleOnEmailVerified
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
    public function handle(Verified $event): void
    {
        $user = $event->user;

        // Assigner le rôle "player" si l'utilisateur n'a pas déjà de rôle
        if ($user->roles->isEmpty()) {
            $user->assignRole('player');
            $user->givePermissionTo('manage-tournaments');
            $user->givePermissionTo('join-tournaments');
        }

        // Envoyer une notification à l'administrateur
        $admins = \App\Models\User::role('super-admin')->orWhere(function ($query) {
            $query->role('admin');
        })->get();

        Notification::send($admins, new NewPlayerRegistered($user));
    }
}
