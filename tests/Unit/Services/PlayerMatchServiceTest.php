<?php

namespace Tests\Unit\Services;

use App\Models\PlayerMatch;
use App\Models\User;
use App\Services\PlayerMatchService;
use App\Services\MatchPermissionService;
use Tests\TestCase;

class PlayerMatchServiceTest extends TestCase
{
    protected PlayerMatchService $playerMatchService;
    protected MatchPermissionService $permissionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->playerMatchService = new PlayerMatchService();
        $this->permissionService = new MatchPermissionService();
    }

    // ==================== Tests PlayerMatchService ====================

    /**
     * Test: Obtenir les matchs disponibles
     */
    public function test_get_available_matches_returns_only_open_validated_matches()
    {
        // Créer un utilisateur
        $user = User::factory()->create();

        // Créer un match ouvert et validé
        $openValidated = PlayerMatch::factory()->create([
            'status' => 'open',
            'is_setup_validated' => true,
            'opponent_id' => null,
        ]);

        // Créer un match ouvert mais non validé
        $openNotValidated = PlayerMatch::factory()->create([
            'status' => 'open',
            'is_setup_validated' => false,
            'opponent_id' => null,
        ]);

        // Créer un match confirmé
        $confirmed = PlayerMatch::factory()->create([
            'status' => 'confirmed',
            'is_setup_validated' => true,
        ]);

        // Obtenir les matchs disponibles
        $available = $this->playerMatchService->getAvailableMatches($user);

        // Vérifier que seul le match ouvert et validé est retourné
        $this->assertCount(1, $available);
        $this->assertTrue($available->first()->id === $openValidated->id);
    }

    /**
     * Test: Obtenir les matchs confirmés d'un utilisateur
     */
    public function test_get_confirmed_matches_returns_creator_and_opponent_matches()
    {
        $creator = User::factory()->create();
        $opponent = User::factory()->create();

        // Match où l'utilisateur est créateur
        $creatorMatch = PlayerMatch::factory()->create([
            'creator_id' => $creator->id,
            'opponent_id' => $opponent->id,
            'status' => 'confirmed',
        ]);

        // Match où l'utilisateur est adversaire
        $opponentMatch = PlayerMatch::factory()->create([
            'creator_id' => $opponent->id,
            'opponent_id' => $creator->id,
            'status' => 'confirmed',
        ]);

        // Match confirmé où l'utilisateur n'est pas impliqué
        $otherMatch = PlayerMatch::factory()->create([
            'status' => 'confirmed',
        ]);

        // Obtenir les matchs confirmés du créateur
        $confirmed = $this->playerMatchService->getConfirmedMatches($creator);

        // Vérifier que les deux matchs sont retournés
        $this->assertCount(2, $confirmed);
        $this->assertTrue($confirmed->pluck('id')->contains($creatorMatch->id));
        $this->assertTrue($confirmed->pluck('id')->contains($opponentMatch->id));
        $this->assertFalse($confirmed->pluck('id')->contains($otherMatch->id));
    }

    /**
     * Test: Obtenir les statistiques d'un utilisateur
     */
    public function test_get_user_stats_calculates_correctly()
    {
        $user = User::factory()->create();
        $opponent = User::factory()->create();

        // Créer 3 matchs terminés
        // 2 victoires
        PlayerMatch::factory()->create([
            'creator_id' => $user->id,
            'opponent_id' => $opponent->id,
            'status' => 'completed',
            'winner_id' => $user->id,
            'is_draw' => false,
        ]);

        PlayerMatch::factory()->create([
            'creator_id' => $opponent->id,
            'opponent_id' => $user->id,
            'status' => 'completed',
            'winner_id' => $user->id,
            'is_draw' => false,
        ]);

        // 1 défaite
        PlayerMatch::factory()->create([
            'creator_id' => $user->id,
            'opponent_id' => $opponent->id,
            'status' => 'completed',
            'winner_id' => $opponent->id,
            'is_draw' => false,
        ]);

        // Obtenir les statistiques
        $stats = $this->playerMatchService->getUserStats($user);

        // Vérifier les statistiques
        $this->assertEquals(3, $stats['total_matches']);
        $this->assertEquals(2, $stats['wins']);
        $this->assertEquals(1, $stats['losses']);
        $this->assertEquals(0, $stats['draws']);
        $this->assertEquals(66.7, $stats['win_ratio']);
    }

    // ==================== Tests MatchPermissionService ====================

    /**
     * Test: canJoin retourne true pour un match valide
     */
    public function test_can_join_returns_true_for_valid_match()
    {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();

        $match = PlayerMatch::factory()->create([
            'creator_id' => $creator->id,
            'opponent_id' => null,
            'status' => 'open',
            'is_setup_validated' => true,
            'available_at' => now()->addDay(),
        ]);

        $this->assertTrue($this->permissionService->canJoin($match, $joiner));
    }

    /**
     * Test: canJoin retourne false si le match n'est pas validé
     */
    public function test_can_join_returns_false_if_setup_not_validated()
    {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();

        $match = PlayerMatch::factory()->create([
            'creator_id' => $creator->id,
            'opponent_id' => null,
            'status' => 'open',
            'is_setup_validated' => false,
        ]);

        $this->assertFalse($this->permissionService->canJoin($match, $joiner));
    }

    /**
     * Test: canJoin retourne false si l'utilisateur est le créateur
     */
    public function test_can_join_returns_false_if_user_is_creator()
    {
        $creator = User::factory()->create();

        $match = PlayerMatch::factory()->create([
            'creator_id' => $creator->id,
            'opponent_id' => null,
            'status' => 'open',
            'is_setup_validated' => true,
        ]);

        $this->assertFalse($this->permissionService->canJoin($match, $creator));
    }

    /**
     * Test: canSetScore retourne true pour le créateur d'un match confirmé
     */
    public function test_can_set_score_returns_true_for_creator_of_confirmed_match()
    {
        $creator = User::factory()->create();
        $opponent = User::factory()->create();

        $match = PlayerMatch::factory()->create([
            'creator_id' => $creator->id,
            'opponent_id' => $opponent->id,
            'status' => 'confirmed',
        ]);

        $this->assertTrue($this->permissionService->canSetScore($match, $creator));
    }

    /**
     * Test: canSetScore retourne false pour un match non confirmé
     */
    public function test_can_set_score_returns_false_for_non_confirmed_match()
    {
        $creator = User::factory()->create();

        $match = PlayerMatch::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'open',
        ]);

        $this->assertFalse($this->permissionService->canSetScore($match, $creator));
    }

    /**
     * Test: canEdit retourne true pour le créateur d'un match non validé
     */
    public function test_can_edit_returns_true_for_creator_of_non_validated_match()
    {
        $creator = User::factory()->create();

        $match = PlayerMatch::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'open',
            'is_setup_validated' => false,
        ]);

        $this->assertTrue($this->permissionService->canEdit($match, $creator));
    }

    /**
     * Test: canEdit retourne false si le match est validé
     */
    public function test_can_edit_returns_false_if_setup_validated()
    {
        $creator = User::factory()->create();

        $match = PlayerMatch::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'open',
            'is_setup_validated' => true,
        ]);

        $this->assertFalse($this->permissionService->canEdit($match, $creator));
    }

    /**
     * Test: getErrorMessage retourne un message approprié
     */
    public function test_get_error_message_returns_appropriate_message()
    {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();

        $match = PlayerMatch::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'open',
            'is_setup_validated' => false,
        ]);

        $message = $this->permissionService->getErrorMessage($match, $joiner, 'join');
        $this->assertEquals('Ce match n\'est pas encore configuré', $message);
    }
}
