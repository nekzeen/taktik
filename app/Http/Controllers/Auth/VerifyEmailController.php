<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        // Vérifier que la signature est valide
        if (!URL::hasValidSignature($request)) {
            abort(403, 'Lien de vérification invalide ou expiré.');
        }

        // Récupérer l'utilisateur
        $user = User::findOrFail($id);

        // Vérifier que le hash correspond à l'email
        if (sha1($user->email) !== $hash) {
            abort(403, 'Lien de vérification invalide.');
        }

        // Si déjà vérifié, rediriger
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        // Marquer l'email comme vérifié
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Connecter l'utilisateur s'il ne l'est pas déjà
        if (!Auth::check()) {
            Auth::login($user, remember: false);
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
