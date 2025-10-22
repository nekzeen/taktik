<?php

namespace App\Filament\Resources\TournamentResource\Pages;

use App\Filament\Resources\TournamentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateTournament extends CreateRecord
{
    protected static string $resource = TournamentResource::class;

    public function mount(): void
    {
        $user = auth()->user();
        
        // Vérifier si l'utilisateur peut créer un tournoi
        if (!$user->can('create', $this->getModel())) {
            $openCount = $user->countOpenTournaments();
            $limit = $this->getLimit($user);
            
            Notification::make()
                ->title('Limite atteinte')
                ->body("Vous avez déjà $openCount tournoi(s) ouvert(s). Limite : $limit.")
                ->danger()
                ->send();
            
            redirect()->route('filament.admin.resources.tournaments.index');
        }
        
        parent::mount();
    }

    private function getLimit($user): int
    {
        if ($user->hasRole('super-admin')) {
            return 999; // Pas de limite
        }
        if ($user->hasRole('admin')) {
            return 10;
        }
        if ($user->hasRole('player')) {
            return 1;
        }
        return 0;
    }
}
