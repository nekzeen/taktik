<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate reCAPTCHA
        $token = $request->input('g-recaptcha-response');
        if ($token) {
            $recaptchaService = new RecaptchaService();
            if (!$recaptchaService->verify($token)) {
                return back()->withErrors(['g-recaptcha-response' => 'La vérification reCAPTCHA a échoué. Veuillez réessayer.']);
            }
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Vérifier que l'email n'existe que chez les utilisateurs actifs (non soft-deleted)
                    $exists = User::where('email', $value)->whereNull('deleted_at')->exists();
                    if ($exists) {
                        $fail('La valeur du champ adresse email est déjà utilisée.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // Rediriger vers la page de vérification d'email avec un message
        return redirect(route('verification.notice'))
            ->with('status', 'Un email de confirmation a été envoyé à ' . $user->email . '. Veuillez vérifier votre boîte de réception et cliquer sur le lien de confirmation.');
    }
}
