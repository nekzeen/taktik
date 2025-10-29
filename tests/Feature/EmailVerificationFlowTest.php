<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test le flux complet d'inscription et vérification d'email
     */
    public function test_complete_email_verification_flow(): void
    {
        // 1. Créer un utilisateur via l'inscription
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        // Vérifier que l'utilisateur a été créé
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        echo "✅ Étape 1 : Utilisateur créé (ID: {$user->id})\n";

        // 2. Vérifier que l'email n'est pas encore vérifié
        $this->assertFalse($user->hasVerifiedEmail());
        echo "✅ Étape 2 : Email non vérifié\n";

        // 3. Vérifier que l'utilisateur n'a pas le rôle "player" avant vérification
        $this->assertFalse($user->hasRole('player'));
        echo "✅ Étape 3 : Utilisateur n'a pas le rôle 'player'\n";

        // 4. Générer le lien de vérification
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        echo "✅ Étape 4 : Lien de vérification généré\n";

        // 5. Accéder au lien de vérification SANS être authentifié
        $response = $this->get($verificationUrl);
        
        // Vérifier que la réponse est une redirection (pas 403)
        $this->assertNotEquals(403, $response->status());
        $this->assertTrue($response->status() === 302 || $response->status() === 200);
        echo "✅ Étape 5 : Lien de vérification accessible (Status: {$response->status()})\n";

        // 6. Vérifier que l'email est maintenant vérifié
        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        echo "✅ Étape 6 : Email vérifié\n";

        // 7. Vérifier que l'utilisateur a le rôle "player"
        $user->refresh();
        $this->assertTrue($user->hasRole('player'));
        echo "✅ Étape 7 : Utilisateur a le rôle 'player'\n";

        // 8. Vérifier que l'utilisateur a les permissions
        $this->assertTrue($user->hasPermissionTo('manage-tournaments'));
        $this->assertTrue($user->hasPermissionTo('join-tournaments'));
        echo "✅ Étape 8 : Utilisateur a les permissions\n";

        // 9. Vérifier que l'utilisateur est connecté après vérification
        $this->assertAuthenticatedAs($user);
        echo "✅ Étape 9 : Utilisateur connecté automatiquement\n";

        echo "\n✅ FLUX COMPLET RÉUSSI !\n";
    }

    /**
     * Test que la vérification d'email invalide échoue
     */
    public function test_invalid_email_verification_fails(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Essayer de vérifier avec un hash invalide
        $invalidUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid_hash']
        );

        $response = $this->get($invalidUrl);
        
        // Devrait être rejeté (403 ou 302 vers login)
        $this->assertTrue($response->status() === 403 || $response->status() === 302);
        echo "✅ Vérification invalide correctement rejetée\n";
    }

    /**
     * Test que la vérification d'email expiré échoue
     */
    public function test_expired_email_verification_fails(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Créer un lien expiré
        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinutes(1), // Expiré il y a 1 minute
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($expiredUrl);
        
        // Devrait être rejeté
        $this->assertTrue($response->status() === 403 || $response->status() === 302);
        echo "✅ Vérification expirée correctement rejetée\n";
    }

    /**
     * Test que l'email de vérification est envoyé
     */
    public function test_verification_email_is_sent(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test.mail@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'test.mail@example.com')->first();
        
        // Vérifier que l'email de vérification a été envoyé
        \Illuminate\Support\Facades\Mail::assertSent(\Illuminate\Auth\Notifications\VerifyEmail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        echo "✅ Email de vérification envoyé\n";
    }
}
