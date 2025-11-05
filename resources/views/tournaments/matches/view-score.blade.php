@extends('layouts.public')

@php
use Illuminate\Support\Facades\DB;
@endphp

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 overflow-hidden shadow-md sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-white">
                            Visualisation du Score - Tournoi
                        </h2>
                        <p class="text-red-100 mt-1">
                            Match entre {{ $match->player1->name }} et {{ $match->player2->name }}
                        </p>
                    </div>
                    <a href="{{ route('tournaments.matches.index', $tournament) }}" 
                       class="text-white hover:text-red-100 font-semibold">
                        ← Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Scores Temporaires des Deux Joueurs -->
        <div class="bg-white border border-gray-300 rounded-lg shadow-md p-4 md:p-6 mb-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-6 text-center">Scores Temporaires</h3>
            
            <!-- Affichage des scores en ligne -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 md:gap-8">
                <!-- Scores du Créateur -->
                <div class="flex-1 w-full">
                    <h4 class="text-sm md:text-base font-bold text-blue-900 mb-4 text-center">{{ $match->player1->name }}</h4>
                    <div class="flex justify-around items-center bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="text-center">
                            <p class="text-xs text-gray-600 font-semibold mb-1">Primaire</p>
                            <p class="text-3xl md:text-4xl font-bold text-blue-600" id="player1-primary-display">-</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600 font-semibold mb-1">Secondaire</p>
                            <p class="text-3xl md:text-4xl font-bold text-blue-600" id="player1-secondary-display">-</p>
                        </div>
                        <div class="text-center bg-green-100 rounded p-3 border border-green-300">
                            <p class="text-xs text-gray-600 font-semibold mb-1">Total</p>
                            <p class="text-3xl md:text-4xl font-bold text-green-700" id="player1-total-display">-</p>
                        </div>
                    </div>
                </div>

                <!-- Séparateur -->
                <div class="hidden md:block w-1 h-20 bg-gray-300"></div>
                <div class="md:hidden w-full h-1 bg-gray-300"></div>

                <!-- Scores de l'Adversaire -->
                <div class="flex-1 w-full">
                    <h4 class="text-sm md:text-base font-bold text-red-900 mb-4 text-center">{{ $match->player2->name }}</h4>
                    <div class="flex justify-around items-center bg-red-50 rounded-lg p-4 border border-red-200">
                        <div class="text-center">
                            <p class="text-xs text-gray-600 font-semibold mb-1">Primaire</p>
                            <p class="text-3xl md:text-4xl font-bold text-red-600" id="player2-primary-display">-</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600 font-semibold mb-1">Secondaire</p>
                            <p class="text-3xl md:text-4xl font-bold text-red-600" id="player2-secondary-display">-</p>
                        </div>
                        <div class="text-center bg-green-100 rounded p-3 border border-green-300">
                            <p class="text-xs text-gray-600 font-semibold mb-1">Total</p>
                            <p class="text-3xl md:text-4xl font-bold text-green-700" id="player2-total-display">-</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <p class="text-xs text-gray-500 mt-4 text-center">⏱️ Mise à jour en temps réel (toutes les 2 secondes)</p>
        </div>

        <!-- Contenu principal -->
        <div class="space-y-4 lg:space-y-6">
            <!-- Missions et Péripéties -->
            <div class="space-y-4 lg:space-y-6">
                <!-- Mission Primaire -->
                @if($match->primaryMission)
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 md:p-8">
                        <button type="button" onclick="document.getElementById('primary-content').classList.toggle('hidden')" class="w-full flex justify-between items-center">
                            <h3 class="text-base md:text-lg font-semibold text-gray-900">Mission Primaire</h3>
                            <span class="text-gray-600 text-lg" id="primary-toggle"></span>
                        </button>
                        
                        <div id="primary-content" class="mt-3 md:mt-4">
                            <!-- Titre -->
                            <div class="mb-3 md:mb-4">
                                @php
                                    $primaryFr = DB::table('translations')
                                        ->where('resource_type', 'PrimaryMission')
                                        ->where('resource_id', $match->primaryMission->id)
                                        ->where('field', 'name')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                    
                                    $primaryFullFr = DB::table('translations')
                                        ->where('resource_type', 'PrimaryMission')
                                        ->where('resource_id', $match->primaryMission->id)
                                        ->where('field', 'full_text')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                @endphp
                                <div class="space-y-2 md:flex md:gap-4 md:space-y-0">
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Anglais</p>
                                        <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $match->primaryMission->name }}</p>
                                    </div>
                                    @if($primaryFr)
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Français</p>
                                            <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $primaryFr }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Texte complet -->
                            <div class="border-t border-gray-200 pt-3 md:pt-4">
                                <div class="flex gap-2 mb-2">
                                    <button type="button" onclick="document.getElementById('primary-en').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">EN</button>
                                    @if($primaryFullFr)
                                        <button type="button" onclick="document.getElementById('primary-fr').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">FR</button>
                                    @endif
                                </div>
                                <div id="primary-en" class="bg-gray-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto border border-gray-200">
                                    <p class="text-xs md:text-sm text-gray-700 whitespace-pre-wrap">{{ $match->primaryMission->full_text }}</p>
                                </div>
                                @if($primaryFullFr)
                                    <div id="primary-fr" class="hidden bg-amber-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto border border-amber-200">
                                        <p class="text-xs md:text-sm text-amber-900 whitespace-pre-wrap">{{ $primaryFullFr }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Mission Secondaire -->
                @if($match->secondaryMission)
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 md:p-8">
                        <button type="button" onclick="document.getElementById('secondary-content').classList.toggle('hidden')" class="w-full flex justify-between items-center">
                            <h3 class="text-base md:text-lg font-semibold text-gray-900">Mission Secondaire</h3>
                            <span class="text-gray-600 text-lg" id="secondary-toggle"></span>
                        </button>
                        
                        <div id="secondary-content" class="mt-3 md:mt-4">
                            <!-- Titre -->
                            <div class="mb-3 md:mb-4">
                                @php
                                    $secondaryFr = DB::table('translations')
                                        ->where('resource_type', 'SecondaryMission')
                                        ->where('resource_id', $match->secondaryMission->id)
                                        ->where('field', 'name')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                    
                                    $secondaryFullFr = DB::table('translations')
                                        ->where('resource_type', 'SecondaryMission')
                                        ->where('resource_id', $match->secondaryMission->id)
                                        ->where('field', 'full_text')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                @endphp
                                <div class="space-y-2 md:flex md:gap-4 md:space-y-0">
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Anglais</p>
                                        <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $match->secondaryMission->name }}</p>
                                    </div>
                                    @if($secondaryFr)
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Français</p>
                                            <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $secondaryFr }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Texte complet -->
                            <div class="border-t border-gray-200 pt-3 md:pt-4">
                                <div class="flex gap-2 mb-2">
                                    <button type="button" onclick="document.getElementById('secondary-en').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">EN</button>
                                    @if($secondaryFullFr)
                                        <button type="button" onclick="document.getElementById('secondary-fr').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">FR</button>
                                    @endif
                                </div>
                                <div id="secondary-en" class="bg-gray-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto border border-gray-200">
                                    <p class="text-xs md:text-sm text-gray-700 whitespace-pre-wrap">{{ $match->secondaryMission->full_text }}</p>
                                </div>
                                @if($secondaryFullFr)
                                    <div id="secondary-fr" class="hidden bg-amber-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto border border-amber-200">
                                        <p class="text-xs md:text-sm text-amber-900 whitespace-pre-wrap">{{ $secondaryFullFr }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Péripétie -->
                @if($match->twistMission)
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 md:p-8">
                        <button type="button" onclick="document.getElementById('twist-content').classList.toggle('hidden')" class="w-full flex justify-between items-center">
                            <h3 class="text-base md:text-lg font-semibold text-gray-900">Péripétie</h3>
                            <span class="text-gray-600 text-lg" id="twist-toggle"></span>
                        </button>
                        
                        <div id="twist-content" class="mt-3 md:mt-4">
                            <!-- Titre -->
                            <div class="mb-3 md:mb-4">
                                @php
                                    $twistFr = DB::table('translations')
                                        ->where('resource_type', 'TwistMission')
                                        ->where('resource_id', $match->twistMission->id)
                                        ->where('field', 'name')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                    
                                    $twistFullFr = DB::table('translations')
                                        ->where('resource_type', 'TwistMission')
                                        ->where('resource_id', $match->twistMission->id)
                                        ->where('field', 'full_text')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                @endphp
                                <div class="space-y-2 md:flex md:gap-4 md:space-y-0">
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Anglais</p>
                                        <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $match->twistMission->name }}</p>
                                    </div>
                                    @if($twistFr)
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Français</p>
                                            <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $twistFr }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Texte complet -->
                            <div class="border-t border-gray-200 pt-3 md:pt-4">
                                <div class="flex gap-2 mb-2">
                                    <button type="button" onclick="document.getElementById('twist-en').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">EN</button>
                                    @if($twistFullFr)
                                        <button type="button" onclick="document.getElementById('twist-fr').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">FR</button>
                                    @endif
                                </div>
                                <div id="twist-en" class="bg-gray-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto border border-gray-200">
                                    <p class="text-xs md:text-sm text-gray-700 whitespace-pre-wrap">{{ $match->twistMission->full_text }}</p>
                                </div>
                                @if($twistFullFr)
                                    <div id="twist-fr" class="hidden bg-amber-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto border border-amber-200">
                                        <p class="text-xs md:text-sm text-amber-900 whitespace-pre-wrap">{{ $twistFullFr }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Gestion des Missions Secondaires -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 md:p-8">
                    <button type="button" onclick="document.getElementById('secondary-management-content').classList.toggle('hidden')" class="w-full flex justify-between items-center">
                        <h3 class="text-base md:text-lg font-semibold text-gray-900">Gestion des Missions Secondaires</h3>
                        <span class="text-gray-600 text-lg" id="secondary-management-toggle"></span>
                    </button>
                    
                    <div id="secondary-management-content" class="mt-3 md:mt-4 space-y-3 md:space-y-4">
                        <!-- Type de missions secondaires -->
                        <div class="space-y-2">
                            <label class="block text-xs md:text-sm font-semibold text-gray-900">Type de missions secondaires</label>
                            <div class="space-y-1.5 md:space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="secondary_type" value="fixed" checked class="mr-2 w-4 h-4" onchange="toggleSecondaryType('fixed')">
                                    <span class="text-xs md:text-sm text-gray-700">Missions Fixes</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="secondary_type" value="tactical" class="mr-2 w-4 h-4" onchange="toggleSecondaryType('tactical')">
                                    <span class="text-xs md:text-sm text-gray-700">Missions Tactiques</span>
                                </label>
                            </div>
                        </div>

                        <!-- Missions Fixes (visible par défaut) -->
                        <div id="fixed-missions-section" class="space-y-2">
                            <label class="block text-xs md:text-sm font-semibold text-gray-900">Sélectionner 2 Missions Fixes</label>
                            
                            @php
                                $fixedMissions = \App\Models\SecondaryMission::where('is_active', true)
                                    ->where('can_be_fixed', true)
                                    ->orderBy('name')
                                    ->get();
                            @endphp
                            
                            <!-- Première mission fixe -->
                            <div>
                                <label class="text-xs font-semibold text-gray-600">Mission Fixe 1</label>
                                <select name="fixed_mission_1" class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs md:text-sm" onchange="updateFixedMissions()">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($fixedMissions as $mission)
                                        @php
                                            $missionFr = DB::table('translations')
                                                ->where('resource_type', 'SecondaryMission')
                                                ->where('resource_id', $mission->id)
                                                ->where('field', 'name')
                                                ->where('locale', 'fr')
                                                ->value('translated_text');
                                        @endphp
                                        <option value="{{ $mission->id }}">{{ $missionFr ?? $mission->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Deuxième mission fixe -->
                            <div>
                                <label class="text-xs font-semibold text-gray-600">Mission Fixe 2</label>
                                <select name="fixed_mission_2" class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs md:text-sm" onchange="updateFixedMissions()">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($fixedMissions as $mission)
                                        @php
                                            $missionFr = DB::table('translations')
                                                ->where('resource_type', 'SecondaryMission')
                                                ->where('resource_id', $mission->id)
                                                ->where('field', 'name')
                                                ->where('locale', 'fr')
                                                ->value('translated_text');
                                        @endphp
                                        <option value="{{ $mission->id }}">{{ $missionFr ?? $mission->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <p id="fixed-warning" class="text-xs text-red-600 hidden">⚠️ Les deux missions doivent être différentes</p>
                        </div>

                        <!-- Missions Tactiques (caché par défaut) -->
                        <div id="tactical-missions-section" class="hidden space-y-2">
                            <label class="block text-xs md:text-sm font-semibold text-gray-900">Missions Tactiques</label>
                            
                            <!-- Missions piochées -->
                            <div class="bg-blue-50 border border-blue-200 rounded p-2 space-y-1.5">
                                <p class="text-xs font-semibold text-blue-900">Missions en jeu</p>
                                <div id="tactical-active-missions" class="space-y-1">
                                    <p class="text-xs text-gray-600 italic">Cliquez sur "Piocher" pour commencer</p>
                                </div>
                            </div>
                            
                            <!-- Bouton piocher -->
                            <button type="button" class="w-full bg-blue-600 text-white py-2 rounded text-xs font-semibold hover:bg-blue-700" onclick="drawTacticalMissions()">
                                Piocher 2 missions
                            </button>
                            
                            <!-- Missions défaussées -->
                            <div class="bg-gray-50 border border-gray-200 rounded p-2 space-y-1.5">
                                <p class="text-xs font-semibold text-gray-700">Missions défaussées <span id="discarded-count" class="text-gray-600">(+0 PC)</span></p>
                                <div id="tactical-discarded-missions" class="space-y-1">
                                    <p class="text-xs text-gray-600 italic">Aucune mission défaussée</p>
                                </div>
                            </div>
                            
                            <!-- Missions terminées -->
                            <div class="bg-green-50 border border-green-200 rounded p-2 space-y-1.5">
                                <p class="text-xs font-semibold text-green-700">Missions terminées</p>
                                <div id="tactical-completed-missions" class="space-y-1">
                                    <p class="text-xs text-gray-600 italic">Aucune mission terminée</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Missions Secondaires Sélectionnées -->
                <div id="selected-secondary-missions-section" class="bg-white rounded-lg shadow-md border border-gray-200 p-6 md:p-8 hidden">
                    <button type="button" onclick="document.getElementById('selected-secondary-content').classList.toggle('hidden')" class="w-full flex justify-between items-center">
                        <h3 class="text-base md:text-lg font-semibold text-gray-900">Missions Secondaires Sélectionnées</h3>
                        <span class="text-gray-600 text-lg" id="selected-secondary-toggle"></span>
                    </button>
                    
                    <div id="selected-secondary-content" class="mt-3 md:mt-4 space-y-4">
                        <!-- Missions fixes sélectionnées -->
                        <div id="fixed-missions-display" class="hidden space-y-3">
                            <!-- Rempli dynamiquement par JavaScript -->
                        </div>
                        
                        <!-- Missions tactiques sélectionnées -->
                        <div id="tactical-missions-display" class="hidden space-y-3">
                            <!-- Rempli dynamiquement par JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // ========== POLLING AJAX POUR LES SCORES EN TEMPS RÉEL ==========
    const matchId = {{ $match->id }};
    let pollingInterval;

    // Fonction pour récupérer et afficher les scores temporaires des deux joueurs
    function updateScoresRealtime() {
        fetch(`{{ url('/api/player-matches') }}/${matchId}/get-draft-scores`)
            .then(response => response.json())
            .then(data => {
                if (data && Object.keys(data).length > 0) {
                    // Scores du Créateur
                    const creatorPrimary = parseInt(data.creator_primary_points) || 0;
                    const creatorSecondary = parseInt(data.creator_secondary_points) || 0;
                    const creatorPainting = data.creator_painting_points ? 10 : 0;
                    const creatorTotal = creatorPrimary + creatorSecondary + creatorPainting;
                    
                    // Scores de l'Adversaire
                    const opponentPrimary = parseInt(data.opponent_primary_points) || 0;
                    const opponentSecondary = parseInt(data.opponent_secondary_points) || 0;
                    const opponentPainting = data.opponent_painting_points ? 10 : 0;
                    const opponentTotal = opponentPrimary + opponentSecondary + opponentPainting;
                    
                    // Afficher les scores du Créateur
                    document.getElementById('player1-primary-display').textContent = creatorPrimary;
                    document.getElementById('player1-secondary-display').textContent = creatorSecondary;
                    document.getElementById('player1-total-display').textContent = creatorTotal;
                    
                    // Afficher les scores de l'Adversaire
                    document.getElementById('player2-primary-display').textContent = opponentPrimary;
                    document.getElementById('player2-secondary-display').textContent = opponentSecondary;
                    document.getElementById('player2-total-display').textContent = opponentTotal;
                    
                    console.log('Scores mis à jour:', { 
                        creator: { primary: creatorPrimary, secondary: creatorSecondary, painting: creatorPainting, total: creatorTotal },
                        opponent: { primary: opponentPrimary, secondary: opponentSecondary, painting: opponentPainting, total: opponentTotal }
                    });
                }
            })
            .catch(error => console.error('Erreur chargement scores:', error));
    }

    // Démarrer le polling toutes les 2 secondes
    function startPolling() {
        console.log('🔄 startPolling() appelé');
        updateScoresRealtime(); // Mise à jour immédiate
        pollingInterval = setInterval(updateScoresRealtime, 2000);
        console.log('🔄 Polling des scores démarré - intervalle:', pollingInterval);
    }

    // Arrêter le polling
    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            console.log('⏹️ Polling des scores arrêté');
        }
    }

    // État des missions tactiques
    let tacticalState = {
        active: [],           // Missions en jeu
        discarded: [],        // Missions défaussées (coûtent des PC)
        completed: [],        // Missions terminées (gratuites)
        waitingReplacement: [] // Missions défaussées en attente de remplacement
    };

    @php
        $allSecondaryMissions = \App\Models\SecondaryMission::where('is_active', true)->get();
        $missionsData = [];
        foreach ($allSecondaryMissions as $m) {
            $missionFr = DB::table('translations')
                ->where('resource_type', 'SecondaryMission')
                ->where('resource_id', $m->id)
                ->where('field', 'name')
                ->where('locale', 'fr')
                ->value('translated_text');
            
            $missionFullFr = DB::table('translations')
                ->where('resource_type', 'SecondaryMission')
                ->where('resource_id', $m->id)
                ->where('field', 'full_text')
                ->where('locale', 'fr')
                ->value('translated_text');
            
            $missionsData[] = [
                'id' => $m->id,
                'name_en' => $m->name,
                'name_fr' => $missionFr ?? $m->name,
                'full_text_en' => $m->full_text,
                'full_text_fr' => $missionFullFr ?? $m->full_text,
            ];
        }
    @endphp
    
    const allMissions = @json($missionsData);
    const storageKey = `match_${matchId}_opponent_scoring_data`;

    // ========== SYSTÈME DE SAUVEGARDE AUTOMATIQUE ==========
    
    // Charger les données sauvegardées au chargement de la page
    function loadSavedData() {
        const saved = localStorage.getItem(storageKey);
        if (!saved) return;
        
        try {
            const data = JSON.parse(saved);
            
            // Restaurer le type de missions secondaires
            if (data.secondaryType) {
                const typeRadio = document.querySelector(`input[name="secondary_type"][value="${data.secondaryType}"]`);
                if (typeRadio) {
                    typeRadio.checked = true;
                    toggleSecondaryType(data.secondaryType);
                }
            }
            
            // Restaurer les missions fixes sélectionnées
            if (data.fixedMission1) {
                document.querySelector('select[name="fixed_mission_1"]').value = data.fixedMission1;
            }
            if (data.fixedMission2) {
                document.querySelector('select[name="fixed_mission_2"]').value = data.fixedMission2;
            }
            
            // Restaurer l'état des missions tactiques
            if (data.tacticalState) {
                tacticalState = data.tacticalState;
                updateTacticalDisplay();
                displaySelectedSecondaryMissions();
            }
            
            console.log('Données restaurées depuis localStorage');
        } catch (e) {
            console.error('Erreur lors de la restauration des données:', e);
        }
    }
    
    // Sauvegarder les données automatiquement
    function saveData() {
        try {
            const data = {
                secondaryType: document.querySelector('input[name="secondary_type"]:checked')?.value,
                fixedMission1: document.querySelector('select[name="fixed_mission_1"]').value,
                fixedMission2: document.querySelector('select[name="fixed_mission_2"]').value,
                tacticalState: tacticalState,
                savedAt: new Date().toISOString()
            };
            
            localStorage.setItem(storageKey, JSON.stringify(data));
            console.log('Données sauvegardées');
        } catch (e) {
            console.error('Erreur lors de la sauvegarde:', e);
        }
    }
    
    // Effacer les données sauvegardées
    function clearSavedData() {
        localStorage.removeItem(storageKey);
        console.log('🗑️ Données sauvegardées supprimées');
    }
    
    // Ajouter des écouteurs pour la sauvegarde automatique
    function setupAutoSave() {
        // Sauvegarde sur changement de type de missions
        document.querySelectorAll('input[name="secondary_type"]').forEach(radio => {
            radio.addEventListener('change', saveData);
        });
        
        // Sauvegarde sur changement de missions fixes
        document.querySelector('select[name="fixed_mission_1"]').addEventListener('change', saveData);
        document.querySelector('select[name="fixed_mission_2"]').addEventListener('change', saveData);
    }

    // Gérer le changement de type de missions secondaires
    function toggleSecondaryType(type) {
        const fixedSection = document.getElementById('fixed-missions-section');
        const tacticalSection = document.getElementById('tactical-missions-section');

        if (type === 'fixed') {
            fixedSection.classList.remove('hidden');
            tacticalSection.classList.add('hidden');
        } else {
            fixedSection.classList.add('hidden');
            tacticalSection.classList.remove('hidden');
        }
        
        // Afficher les missions sélectionnées
        displaySelectedSecondaryMissions();
    }

    // Mettre à jour les missions fixes (validation sans doublons)
    function updateFixedMissions() {
        const select1 = document.querySelector('select[name="fixed_mission_1"]');
        const select2 = document.querySelector('select[name="fixed_mission_2"]');
        const warning = document.getElementById('fixed-warning');

        if (select1.value && select2.value && select1.value === select2.value) {
            warning.classList.remove('hidden');
            select2.value = '';
        } else {
            warning.classList.add('hidden');
        }
        
        // Afficher les missions sélectionnées
        displaySelectedSecondaryMissions();
    }
    
    // Afficher les missions secondaires sélectionnées
    function displaySelectedSecondaryMissions() {
        const select1 = document.querySelector('select[name="fixed_mission_1"]');
        const select2 = document.querySelector('select[name="fixed_mission_2"]');
        const section = document.getElementById('selected-secondary-missions-section');
        const fixedDisplay = document.getElementById('fixed-missions-display');
        const tacticalDisplay = document.getElementById('tactical-missions-display');
        
        const secondaryType = document.querySelector('input[name="secondary_type"]:checked').value;
        
        if (secondaryType === 'fixed') {
            // Afficher les missions fixes sélectionnées
            fixedDisplay.innerHTML = '';
            fixedDisplay.classList.remove('hidden');
            tacticalDisplay.classList.add('hidden');
            
            const selectedIds = [select1.value, select2.value].filter(id => id);
            
            if (selectedIds.length === 0) {
                section.classList.add('hidden');
                return;
            }
            
            selectedIds.forEach(missionId => {
                const mission = allMissions.find(m => m.id == missionId);
                if (mission) {
                    fixedDisplay.innerHTML += createMissionDisplay(mission);
                }
            });
            
            section.classList.remove('hidden');
        } else {
            // Afficher les missions tactiques
            tacticalDisplay.innerHTML = '';
            tacticalDisplay.classList.remove('hidden');
            fixedDisplay.classList.add('hidden');
            
            if (tacticalState.active.length === 0 && tacticalState.discarded.length === 0 && tacticalState.completed.length === 0) {
                section.classList.add('hidden');
                return;
            }
            
            // Afficher missions en jeu
            if (tacticalState.active.length > 0) {
                tacticalDisplay.innerHTML += '<div class="font-semibold text-sm text-blue-900 mb-2">Missions en jeu</div>';
                tacticalState.active.forEach(mission => {
                    tacticalDisplay.innerHTML += createMissionDisplay(mission, true); // isActive = true
                });
            }
            
            section.classList.remove('hidden');
        }
    }
    
    // Créer l'affichage d'une mission avec texte complet (seulement pour les missions actives)
    function createMissionDisplay(mission, isActive = false) {
        let html = `
            <div class="border-b border-gray-200 pb-3">
                <div class="mb-2">
                    <div class="space-y-2 md:flex md:gap-4 md:space-y-0">
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Anglais</p>
                            <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">${mission.name_en}</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Français</p>
                            <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">${mission.name_fr}</p>
                        </div>
                    </div>
                </div>`;
        
        // Afficher le texte complet seulement pour les missions actives
        if (isActive) {
            html += `
                <div class="border-t border-gray-200 pt-2">
                    <div class="flex gap-2 mb-2">
                        <button type="button" onclick="document.getElementById('mission-${mission.id}-en').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">EN</button>
                        <button type="button" onclick="document.getElementById('mission-${mission.id}-fr').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">FR</button>
                    </div>
                    <div id="mission-${mission.id}-en" class="bg-gray-50 p-2 md:p-3 rounded-lg max-h-48 md:max-h-64 overflow-y-auto border border-gray-200">
                        <p class="text-xs md:text-sm text-gray-700 whitespace-pre-wrap">${mission.full_text_en}</p>
                    </div>
                    <div id="mission-${mission.id}-fr" class="hidden bg-amber-50 p-2 md:p-3 rounded-lg max-h-48 md:max-h-64 overflow-y-auto border border-amber-200">
                        <p class="text-xs md:text-sm text-amber-900 whitespace-pre-wrap">${mission.full_text_fr}</p>
                    </div>
                </div>`;
        }
        
        html += `</div>`;
        return html;
    }

    // Piocher des missions tactiques aléatoires
    function drawTacticalMissions() {
        // Missions disponibles (pas déjà piochées)
        const usedIds = [...tacticalState.active, ...tacticalState.discarded, ...tacticalState.completed].map(m => m.id);
        const available = allMissions.filter(m => !usedIds.includes(m.id));

        if (available.length < 2) {
            alert('Pas assez de missions disponibles !');
            return;
        }

        // Piocher 2 missions aléatoires
        const shuffled = available.sort(() => 0.5 - Math.random());
        const newMissions = shuffled.slice(0, 2);

        tacticalState.active = newMissions;
        updateTacticalDisplay();
        displaySelectedSecondaryMissions();
        saveData(); // Sauvegarder après changement
        saveTacticalState(); // Sauvegarder l'état tactique en base de données
    }

    // Défausser une mission (coûte 1 PC)
    function discardMission(missionId) {
        const mission = tacticalState.active.find(m => m.id === missionId);
        if (mission) {
            tacticalState.active = tacticalState.active.filter(m => m.id !== missionId);
            tacticalState.discarded.push(mission);
            
            // Marquer comme en attente de remplacement (le joueur doit cliquer pour générer une nouvelle)
            tacticalState.waitingReplacement.push({
                id: 'waiting_' + Date.now(),
                discardedMissionId: missionId,
                status: 'waiting'
            });
            
            updateTacticalDisplay();
            displaySelectedSecondaryMissions();
            saveData(); // Sauvegarder après changement
            saveTacticalState(); // Sauvegarder l'état tactique en base de données
        }
    }

    // Marquer une mission comme terminée (gratuit)
    function completeMission(missionId) {
        const mission = tacticalState.active.find(m => m.id === missionId);
        if (mission) {
            tacticalState.active = tacticalState.active.filter(m => m.id !== missionId);
            tacticalState.completed.push(mission);
            
            // Régénérer une nouvelle mission aléatoire pour remplacer celle terminée
            const usedIds = [...tacticalState.active, ...tacticalState.discarded, ...tacticalState.completed].map(m => m.id);
            const available = allMissions.filter(m => !usedIds.includes(m.id));
            
            if (available.length > 0) {
                // Piocher 1 mission aléatoire
                const randomIndex = Math.floor(Math.random() * available.length);
                const newMission = available[randomIndex];
                tacticalState.active.push(newMission);
            }
            
            updateTacticalDisplay();
            displaySelectedSecondaryMissions();
            saveData(); // Sauvegarder après changement
            saveTacticalState(); // Sauvegarder l'état tactique en base de données
        }
    }
    
    // Générer une nouvelle mission pour remplacer celle défaussée
    function generateReplacementMission(waitingId) {
        const usedIds = [...tacticalState.active, ...tacticalState.discarded, ...tacticalState.completed].map(m => m.id);
        const available = allMissions.filter(m => !usedIds.includes(m.id));
        
        if (available.length === 0) {
            // Marquer comme "deck épuisé" au lieu de générer une nouvelle mission
            tacticalState.waitingReplacement = tacticalState.waitingReplacement.map(w => 
                w.id === waitingId ? { ...w, status: 'exhausted' } : w
            );
            updateTacticalDisplay();
            displaySelectedSecondaryMissions();
            saveData(); // Sauvegarder après changement
            saveTacticalState(); // Sauvegarder l'état tactique en base de données
            return;
        }
        
        // Piocher 1 mission aléatoire
        const randomIndex = Math.floor(Math.random() * available.length);
        const newMission = available[randomIndex];
        
        // Ajouter à active et supprimer de waitingReplacement
        tacticalState.active.push(newMission);
        tacticalState.waitingReplacement = tacticalState.waitingReplacement.filter(w => w.id !== waitingId);
        
        updateTacticalDisplay();
        displaySelectedSecondaryMissions();
        saveData(); // Sauvegarder après changement
        saveTacticalState(); // Sauvegarder l'état tactique en base de données
    }

    // Afficher les missions tactiques
    function updateTacticalDisplay() {
        // Missions en jeu
        const activeDiv = document.getElementById('tactical-active-missions');
        let activeHtml = '';
        
        // Afficher les missions actives
        if (tacticalState.active.length > 0) {
            activeHtml += tacticalState.active.map(mission => `
                <div class="bg-white border border-blue-300 rounded p-2 flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-900">${mission.name_fr}</p>
                        <p class="text-xs text-gray-600">${mission.name_en}</p>
                    </div>
                    <div class="flex gap-1">
                        <button type="button" class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded hover:bg-red-200" onclick="discardMission(${mission.id})">
                            Défausser (+1 PC)
                        </button>
                        <button type="button" class="text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded hover:bg-green-200" onclick="completeMission(${mission.id})">
                            Terminée
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        // Afficher les missions en attente de remplacement (gris)
        if (tacticalState.waitingReplacement.length > 0) {
            activeHtml += tacticalState.waitingReplacement.map(waiting => {
                if (waiting.status === 'exhausted') {
                    return `
                        <div class="bg-gray-300 border border-gray-500 rounded p-2">
                            <p class="text-xs font-semibold text-gray-700">Deck épuisé</p>
                        </div>
                    `;
                } else {
                    return `
                        <div class="bg-gray-200 border border-gray-400 rounded p-2 cursor-pointer hover:bg-gray-300 transition" onclick="generateReplacementMission('${waiting.id}')">
                            <p class="text-xs font-semibold text-gray-700">Mission défaussée</p>
                            <p class="text-xs text-gray-600">Cliquer pour en générer une nouvelle</p>
                        </div>
                    `;
                }
            }).join('');
        }
        
        if (activeHtml === '') {
            activeDiv.innerHTML = '<p class="text-xs text-gray-600 italic">Cliquez sur "Piocher" pour commencer</p>';
        } else {
            activeDiv.innerHTML = activeHtml;
        }

        // Missions défaussées
        const discardedDiv = document.getElementById('tactical-discarded-missions');
        const discardedCount = document.getElementById('discarded-count');
        if (tacticalState.discarded.length === 0) {
            discardedDiv.innerHTML = '<p class="text-xs text-gray-600 italic">Aucune mission défaussée</p>';
            discardedCount.textContent = '(+0 PC)';
        } else {
            discardedDiv.innerHTML = tacticalState.discarded.map(mission => `
                <div class="bg-white border border-gray-300 rounded p-2">
                    <p class="text-xs font-semibold text-gray-900">${mission.name_fr}</p>
                    <p class="text-xs text-gray-600">${mission.name_en}</p>
                </div>
            `).join('');
            discardedCount.textContent = `(+${tacticalState.discarded.length} PC)`;
        }

        // Missions terminées
        const completedDiv = document.getElementById('tactical-completed-missions');
        if (tacticalState.completed.length === 0) {
            completedDiv.innerHTML = '<p class="text-xs text-gray-600 italic">Aucune mission terminée</p>';
        } else {
            completedDiv.innerHTML = tacticalState.completed.map(mission => `
                <div class="bg-white border border-green-300 rounded p-2">
                    <p class="text-xs font-semibold text-gray-900">${mission.name_fr}</p>
                    <p class="text-xs text-gray-600">${mission.name_en}</p>
                </div>
            `).join('');
        }
    }

    // ========== SAUVEGARDE DE L'ÉTAT TACTIQUE (MISSIONS SECONDAIRES) ==========
    let tacticalAutoSaveTimeout;

    // Charger l'état tactique depuis la base de données
    function loadTacticalState() {
        fetch(`{{ url('/api/player-matches') }}/${matchId}/get-tactical-state/opponent`)
            .then(response => response.json())
            .then(data => {
                console.log('📥 État tactique chargé:', data);
                if (data.active || data.discarded || data.completed) {
                    tacticalState = data;
                    updateTacticalDisplay();
                    displaySelectedSecondaryMissions();
                }
            })
            .catch(error => console.error('Erreur chargement état tactique:', error));
    }

    // Sauvegarder l'état tactique en base de données
    function saveTacticalState() {
        clearTimeout(tacticalAutoSaveTimeout);
        
        tacticalAutoSaveTimeout = setTimeout(() => {
            try {
                const csrfToken = document.querySelector('input[name="_token"]')?.value;
                if (!csrfToken) {
                    console.error('Token CSRF non trouvé');
                    return;
                }

                fetch(`{{ url('/api/player-matches') }}/${matchId}/save-tactical-state/opponent`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(tacticalState),
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        console.log('État tactique sauvegardé');
                    } else {
                        console.error('Erreur:', result.error);
                    }
                })
                .catch(error => console.error('Erreur sauvegarde état tactique:', error));
            } catch (e) {
                console.error('Erreur:', e);
            }
        }, 2000);
    }

    // Initialiser au chargement
    document.addEventListener('DOMContentLoaded', function() {
        // Charger les données sauvegardées
        loadSavedData();
        loadTacticalState();
        
        // Mettre à jour l'affichage
        updateFixedMissions();
        
        // Configurer la sauvegarde automatique
        setupAutoSave();
        
        // Démarrer le polling des scores en temps réel
        startPolling();
    });

    // Arrêter le polling quand la page se ferme
    window.addEventListener('beforeunload', function() {
        stopPolling();
    });
</script>
@endsection
