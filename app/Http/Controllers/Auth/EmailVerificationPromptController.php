<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();
        
        // Si l'utilisateur n'est pas authentifié, afficher la page de vérification
        if (!$user) {
            return view('auth.verify-email');
        }
        
        // Si l'utilisateur est authentifié et son email est vérifié, rediriger
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }
        
        // Sinon, afficher la page de vérification
        return view('auth.verify-email');
    }
}
