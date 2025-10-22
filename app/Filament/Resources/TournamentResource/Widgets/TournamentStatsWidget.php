<?php

namespace App\Filament\Resources\TournamentResource\Widgets;

use App\Models\Tournament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TournamentStatsWidget extends BaseWidget
{
    public ?Tournament $record = null;
    
    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }
        
        // Statistiques des listes d'armée
        $totalLists = $this->record->armyLists()->count();
        $validatedLists = $this->record->armyLists()->where('status', 'validated')->count();
        $pendingLists = $this->record->armyLists()->where('status', 'pending')->count();
        $rejectedLists = $this->record->armyLists()->where('status', 'rejected')->count();
        
        // Statistiques des matchs
        $totalMatches = $this->record->tournamentMatches()->count();
        $completedMatches = $this->record->tournamentMatches()->where('status', 'completed')->count();
        $inProgressMatches = $this->record->tournamentMatches()->where('status', 'in_progress')->count();
        $pendingMatches = $this->record->tournamentMatches()->where('status', 'pending')->count();
        
        $stats = [
            Stat::make('Total des inscriptions', $totalLists)
                ->description('Nombre total de listes d\'armée')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            
            Stat::make('Listes validées', $validatedLists)
                ->description('Prêtes pour le tournoi')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('En attente de validation', $pendingLists)
                ->description('À valider')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
        
        if ($rejectedLists > 0) {
            $stats[] = Stat::make('Listes rejetées', $rejectedLists)
                ->description('Nécessitent une correction')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger');
        }
        
        // Ajouter les places restantes si max_players est défini
        if ($this->record->max_players) {
            $remainingSlots = $this->record->max_players - $totalLists;
            $stats[] = Stat::make('Places restantes', max(0, $remainingSlots))
                ->description("Sur {$this->record->max_players} places")
                ->descriptionIcon('heroicon-m-users')
                ->color($remainingSlots > 0 ? 'info' : 'danger');
        }
        
        // Statistiques des matchs
        if ($totalMatches > 0) {
            $stats[] = Stat::make('Total des matchs', $totalMatches)
                ->description('Matchs créés')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info');
            
            $stats[] = Stat::make('Matchs terminés', $completedMatches)
                ->description("Sur {$totalMatches} matchs")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success');
            
            if ($inProgressMatches > 0) {
                $stats[] = Stat::make('Matchs en cours', $inProgressMatches)
                    ->description('En train de se jouer')
                    ->descriptionIcon('heroicon-m-play')
                    ->color('warning');
            }
        }
        
        return $stats;
    }
}
