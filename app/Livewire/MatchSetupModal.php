<?php

namespace App\Livewire;

use App\Models\TournamentMatch;
use App\Models\PlayerMatch;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class MatchSetupModal extends Component
{
    public $match;
    public $matchType;

    public function mount($match)
    {
        $this->match = $match;
        
        // Déterminer le type de match
        if ($match instanceof TournamentMatch) {
            $this->matchType = 'tournament';
        } elseif ($match instanceof PlayerMatch) {
            $this->matchType = 'player';
        }
    }

    public function getSetupUrl()
    {
        if ($this->matchType === 'tournament') {
            return route('tournaments.matches.setup', [
                'tournament' => $this->match->tournament,
                'match' => $this->match
            ]);
        } else {
            return route('player-matches.setup', $this->match);
        }
    }

    public function render()
    {
        return view('livewire.match-setup-modal', [
            'setupUrl' => $this->getSetupUrl(),
            'isSetupValidated' => $this->match->is_setup_validated,
        ]);
    }
}
