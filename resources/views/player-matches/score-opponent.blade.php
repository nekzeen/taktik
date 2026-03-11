@extends('layouts.public')

@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Déterminer les permissions
$isCreator = Auth::id() === $playerMatch->creator_id;
$isOpponent = Auth::id() === $playerMatch->opponent_id;
@endphp

@section('content')
<style>
    /* Masquer les boutons +/- pour creator (l'adversaire) dans score-opponent */
    .creator-readonly-buttons button {
        display: none !important;
    }
</style>
<div class="py-12 bg-gray-300">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 overflow-hidden shadow-md sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-white">
                            Saisir le Score - Match Simple
                        </h2>
                        <p class="text-red-100 mt-1">
                            Match entre {{ $playerMatch->creator->name }} et {{ $playerMatch->opponent->name }}
                        </p>
                    </div>
                    <a href="{{ route('player-matches.show', $playerMatch) }}" 
                       class="text-white hover:text-red-100 font-semibold">
                        ← Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Alerte de validation du créateur - Modal centré -->
        <div id="validation-alert" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-red-50 border-4 border-red-300 rounded-xl p-8 max-w-md mx-4 shadow-2xl">
                <div class="text-center">
                    <p class="text-4xl mb-4">🔔</p>
                    <p class="text-red-900 font-bold text-xl mb-2">{{ $playerMatch->creator->name }} a validé le score!</p>
                    <p class="text-red-700 text-base mb-6">Veuillez confirmer pour finaliser le match.</p>
                    <div class="flex gap-3">
                        <button type="button" onclick="validateOpponentScore()" 
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg text-base transition">
                            ✓ Confirmer
                        </button>
                        <button type="button" onclick="rejectValidation()" 
                            class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-6 rounded-lg text-base transition">
                            ✗ Refuser
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @php
            $shouldShowValidationModal = (bool) $playerMatch->creator_score_validated && !(bool) $playerMatch->opponent_score_validated && $playerMatch->status !== 'completed';
        @endphp

        <!-- Message d'attente de validation -->
        <div id="waiting-alert" class="hidden fixed inset-0 flex items-center justify-center z-40 px-4">
            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 w-full max-w-md shadow-lg">
                <p class="text-blue-900 font-semibold">⏳ Score enregistré. En attente de la validation de l'autre joueur...</p>
                <p class="text-blue-700 text-sm mt-1">Vous pouvez fermer cette page, vous serez notifié quand l'adversaire validera.</p>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
            <!-- Colonne gauche : Missions et Péripéties -->
            <div class="lg:col-span-2 space-y-4 lg:space-y-6 order-2 lg:order-1">
                <!-- Mission Primaire -->
                @if($playerMatch->primaryMission)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
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
                                        ->where('resource_id', $playerMatch->primaryMission->id)
                                        ->where('field', 'name')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                    
                                    $primaryFullFr = DB::table('translations')
                                        ->where('resource_type', 'PrimaryMission')
                                        ->where('resource_id', $playerMatch->primaryMission->id)
                                        ->where('field', 'full_text')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                @endphp
                                <div class="space-y-2 md:flex md:gap-4 md:space-y-0">
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Anglais</p>
                                        <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $playerMatch->primaryMission->name }}</p>
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
                                    <p class="text-xs md:text-sm text-gray-700 whitespace-pre-wrap">{{ $playerMatch->primaryMission->full_text }}</p>
                                </div>
                                @if($primaryFullFr)
                                    <div id="primary-fr" class="hidden bg-blue-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto border border-blue-200">
                                        <p class="text-xs md:text-sm text-blue-900 whitespace-pre-wrap">{{ $primaryFullFr }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Mission Secondaire -->
                @if($playerMatch->secondaryMission)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
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
                                        ->where('resource_id', $playerMatch->secondaryMission->id)
                                        ->where('field', 'name')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                    
                                    $secondaryFullFr = DB::table('translations')
                                        ->where('resource_type', 'SecondaryMission')
                                        ->where('resource_id', $playerMatch->secondaryMission->id)
                                        ->where('field', 'full_text')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                @endphp
                                <div class="space-y-2 md:flex md:gap-4 md:space-y-0">
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Anglais</p>
                                        <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $playerMatch->secondaryMission->name }}</p>
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
                                    <p class="text-xs md:text-sm text-gray-700 whitespace-pre-wrap">{{ $playerMatch->secondaryMission->full_text }}</p>
                                </div>
                                @if($secondaryFullFr)
                                    <div id="secondary-fr" class="hidden bg-blue-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto border border-blue-200">
                                        <p class="text-xs md:text-sm text-blue-900 whitespace-pre-wrap">{{ $secondaryFullFr }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Missions Secondaires Sélectionnées -->
                <div id="selected-secondary-missions-section" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hidden">
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

            <!-- Colonne droite : Formulaire de scoring -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 lg:h-fit order-1 lg:order-2">
                <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4">Enregistrer le score</h3>
                
                <form id="score-form" action="{{ route('player-matches.set-score', $playerMatch) }}" method="POST" class="space-y-3 md:space-y-4" onsubmit="submitScoreForm(event); return false;">
                    @csrf
                    <!-- Résultat du match -->
                    <div class="space-y-2">
                        <label class="block text-xs md:text-sm font-semibold text-gray-900">Résultat du match *</label>
                        <input type="hidden" id="creator_result" name="creator_result" value="normal">
                        <div class="space-y-1.5 md:space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="creator_result_special" value="opponent_abandon" class="mr-2 w-4 h-4" onchange="syncCreatorResult(); updateTotals(); validateForm(); toggleFinishMatchHint()">
                                <span class="text-xs md:text-sm text-gray-700">J'abandonne la partie</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="creator_result_special" value="opponent_table_rase" class="mr-2 w-4 h-4" onchange="syncCreatorResult(); updateTotals(); validateForm(); toggleFinishMatchHint()">
                                <span class="text-xs md:text-sm text-gray-700">J'ai subi une table rase</span>
                            </label>
                        </div>

                        <div id="finish-match-hint" class="hidden bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <p class="text-amber-900 text-xs md:text-sm font-semibold">Pour enregistrer ce résultat, cliquez sur le bouton "Fin du match".</p>
                        </div>
                    </div>

                    <hr class="my-2 md:my-4">

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
                            <p class="text-xs font-semibold text-gray-700">Missions défaussées <span id="discarded-count" class="text-gray-600">(-0 PC)</span></p>
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

                    <hr class="my-3 md:my-4">

                    <!-- Points créateur -->
                    <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4 md:p-5 space-y-3">
                        <p class="text-sm md:text-base font-bold text-red-900">{{ $playerMatch->creator->name }}</p>
                        
                        <!-- Points primaires -->
                        <div>
                            <label class="text-xs md:text-sm font-semibold text-red-800 block mb-2">Points primaires (max 50)</label>
                            <div class="flex items-center gap-2 creator-readonly-buttons">
                                <button type="button" onclick="return false; // decrementPoints('creator_primary_points', 50)" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-sm">-</button>
                                <input type="number" id="creator_primary_points" readonly name="creator_primary_points" min="0" max="50" value="0" class="flex-1 px-3 py-2 border-2 border-red-300 rounded-lg text-center text-sm font-semibold focus:border-red-500 focus:ring-2 focus:ring-red-200 transition" required>
                                <button type="button" onclick="return false; // incrementPoints('creator_primary_points', 50)" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-sm">+</button>
                            </div>
                            <p id="creator_primary_error" class="text-xs text-red-600 mt-1 hidden">Maximum 50 points</p>
                        </div>
                        
                        <!-- Points secondaires -->
                        <div>
                            <label class="text-xs md:text-sm font-semibold text-red-800 block mb-2">Points secondaires (max 40)</label>
                            <div class="flex items-center gap-2 creator-readonly-buttons">
                                <button type="button" onclick="return false; // decrementPoints('creator_secondary_points', 40)" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-sm">-</button>
                                <input type="number" id="creator_secondary_points" readonly name="creator_secondary_points" min="0" max="40" value="0" class="flex-1 px-3 py-2 border-2 border-red-300 rounded-lg text-center text-sm font-semibold focus:border-red-500 focus:ring-2 focus:ring-red-200 transition" required>
                                <button type="button" onclick="return false; // incrementPoints('creator_secondary_points', 40)" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-sm">+</button>
                            </div>
                            <p id="creator_secondary_error" class="text-xs text-red-600 mt-1 hidden">Maximum 40 points</p>
                        </div>
                        
                        <label class="flex items-center text-xs md:text-sm">
                            <input type="checkbox" id="creator_painting_points" readonly name="creator_painting_points" value="1" checked class="mr-2 w-4 h-4 accent-red-600">
                            <span class="text-red-900 font-medium">Points peinture (+10)</span>
                        </label>
                        <div class="bg-white border-2 border-red-300 p-3 rounded-lg">
                            <p class="text-sm text-red-900"><span class="font-bold">Total:</span> <span id="creator_total" class="font-bold text-lg text-red-700">10</span> points</p>
                        </div>
                    </div>

                    <hr class="my-3 md:my-4">

                    <!-- Points adversaire -->
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 md:p-5 space-y-3">
                        <p class="text-sm md:text-base font-bold text-blue-900">{{ $playerMatch->opponent->name }}</p>
                        
                        <!-- Points primaires -->
                        <div>
                            <label class="text-xs md:text-sm font-semibold text-blue-800 block mb-2">Points primaires (max 50)</label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="decrementPoints('opponent_primary_points', 50)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-sm">-</button>
                                <input type="number" id="opponent_primary_points" name="opponent_primary_points" min="0" max="50" value="0" class="flex-1 px-3 py-2 border-2 border-blue-300 rounded-lg text-center text-sm font-semibold focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition" required onchange="updateTotals(); validateForm()" oninput="updateTotals(); validateForm()">
                                <button type="button" onclick="incrementPoints('opponent_primary_points', 50)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-sm">+</button>
                            </div>
                            <p id="opponent_primary_error" class="text-xs text-red-600 mt-1 hidden">Maximum 50 points</p>
                        </div>
                        
                        <!-- Points secondaires -->
                        <div>
                            <label class="text-xs md:text-sm font-semibold text-blue-800 block mb-2">Points secondaires (max 40)</label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="decrementPoints('opponent_secondary_points', 40)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-sm">-</button>
                                <input type="number" id="opponent_secondary_points" name="opponent_secondary_points" min="0" max="40" value="0" class="flex-1 px-3 py-2 border-2 border-blue-300 rounded-lg text-center text-sm font-semibold focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition" required onchange="updateTotals(); validateForm()" oninput="updateTotals(); validateForm()">
                                <button type="button" onclick="incrementPoints('opponent_secondary_points', 40)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-sm">+</button>
                            </div>
                            <p id="opponent_secondary_error" class="text-xs text-red-600 mt-1 hidden">Maximum 40 points</p>
                        </div>
                        
                        <label class="flex items-center text-xs md:text-sm">
                            <input type="checkbox" id="opponent_painting_points" name="opponent_painting_points" value="1" checked class="mr-2 w-4 h-4 accent-blue-600" onchange="updateTotals(); validateForm()">
                            <span class="text-blue-900 font-medium">Points peinture (+10)</span>
                        </label>
                        <div class="bg-white border-2 border-blue-300 p-3 rounded-lg">
                            <p class="text-sm text-blue-900"><span class="font-bold">Total:</span> <span id="opponent_total" class="font-bold text-lg text-blue-700">10</span> points</p>
                        </div>
                    </div>

                    <!-- Champ caché pour le résultat de l'adversaire -->
                    <input type="hidden" id="opponent_result" name="opponent_result" value="">

                    <button type="submit" id="submit_btn" class="w-full bg-green-600 text-white py-2 md:py-3 rounded-lg font-semibold hover:bg-green-700 transition text-xs md:text-sm mt-3 md:mt-4" onclick="console.log('Bouton cliqué! Disabled:', this.disabled);">
                        Fin du match
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

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
        $canShuffleBack = str_contains(strtolower((string) ($m->full_text ?? '')), 'shuffle this card back');
        $missionsData[] = [
            'id' => $m->id,
            'name_en' => $m->name,
            'name_fr' => $missionFr ?? $m->name,
            'full_text_en' => $m->full_text,
            'full_text_fr' => DB::table('translations')
                ->where('resource_type', 'SecondaryMission')
                ->where('resource_id', $m->id)
                ->where('field', 'full_text')
                ->where('locale', 'fr')
                ->value('translated_text') ?? $m->full_text,
            'can_shuffle_back' => $canShuffleBack,
        ];
    }
@endphp

<script>
const allMissions = @json($missionsData);
    // État des missions tactiques
    let tacticalState = {
        active: [],           // Missions en jeu
        discarded: [],        // Missions défaussées (coûtent des PC)
        completed: [],        // Missions terminées (gratuites)
        waitingReplacement: [] // Missions défaussées en attente de remplacement
    };

    function hydrateTacticalMission(mission) {
        const full = allMissions.find(m => m.id === mission.id);
        if (!full) {
            return mission;
        }

        return {
            ...full,
            ...mission,
        };
    }

    function normalizeTacticalState() {
        tacticalState.active = (tacticalState.active || []).map(hydrateTacticalMission);
        tacticalState.discarded = (tacticalState.discarded || []).map(hydrateTacticalMission);
        tacticalState.completed = (tacticalState.completed || []).map(hydrateTacticalMission);
    }

    const matchId = {{ $playerMatch->id }};
    const storageKey = `match_${matchId}_scoring_data`;

    // ========== SYSTÈME DE SAUVEGARDE AUTOMATIQUE ==========
    
    // Charger les données sauvegardées au chargement de la page
    function loadSavedData() {
        const saved = localStorage.getItem(storageKey);
        if (!saved) return;
        
        try {
            const data = JSON.parse(saved);
            
            // Restaurer le résultat (normal implicite)
            document.getElementById('creator_result').value = data.creatorResult || 'normal';
            document.querySelectorAll('input[name="creator_result_special"]').forEach(r => r.checked = false);
            if (data.creatorResult && data.creatorResult !== 'normal') {
                const specialRadio = document.querySelector(`input[name="creator_result_special"][value="${data.creatorResult}"]`);
                if (specialRadio) specialRadio.checked = true;
            }
            if (data.creatorPrimaryPoints) {
                document.getElementById('creator_primary_points').value = data.creatorPrimaryPoints;
            }
            if (data.creatorSecondaryPoints) {
                document.getElementById('creator_secondary_points').value = data.creatorSecondaryPoints;
            }
            if (data.creatorPaintingPoints !== undefined) {
                document.getElementById('creator_painting_points').checked = data.creatorPaintingPoints;
            }
            
            if (data.opponentPrimaryPoints) {
                document.getElementById('opponent_primary_points').value = data.opponentPrimaryPoints;
            }
            if (data.opponentSecondaryPoints) {
                document.getElementById('opponent_secondary_points').value = data.opponentSecondaryPoints;
            }
            if (data.opponentPaintingPoints !== undefined) {
                document.getElementById('opponent_painting_points').checked = data.opponentPaintingPoints;
            }
            
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
                normalizeTacticalState();
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
                creatorResult: document.getElementById('creator_result')?.value,
                creatorPrimaryPoints: document.getElementById('creator_primary_points').value,
                creatorSecondaryPoints: document.getElementById('creator_secondary_points').value,
                creatorPaintingPoints: document.getElementById('creator_painting_points').checked,
                opponentPrimaryPoints: document.getElementById('opponent_primary_points').value,
                opponentSecondaryPoints: document.getElementById('opponent_secondary_points').value,
                opponentPaintingPoints: document.getElementById('opponent_painting_points').checked,
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
    
    // Effacer les données sauvegardées après soumission
    function clearSavedData() {
        localStorage.removeItem(storageKey);
        console.log('🗑️ Données sauvegardées supprimées');
    }
    
    // Ajouter des écouteurs pour la sauvegarde automatique
    function setupAutoSave() {
        // Sauvegarde sur changement de résultat spécial
        document.querySelectorAll('input[name="creator_result_special"]').forEach(radio => {
            radio.addEventListener('change', () => {
                syncCreatorResult();
                saveScoringData();
                toggleFinishMatchHint();
            });
        });
        
        // Sauvegarde sur changement de points (change et input pour capturer tous les changements)
        document.getElementById('creator_primary_points').addEventListener('change', saveScoringData);
        document.getElementById('creator_primary_points').addEventListener('input', saveScoringData);
        document.getElementById('creator_secondary_points').addEventListener('change', saveScoringData);
        document.getElementById('creator_secondary_points').addEventListener('input', saveScoringData);
        document.getElementById('creator_painting_points').addEventListener('change', saveScoringData);
        document.getElementById('opponent_primary_points').addEventListener('change', saveScoringData);
        document.getElementById('opponent_primary_points').addEventListener('input', saveScoringData);
        document.getElementById('opponent_secondary_points').addEventListener('change', saveScoringData);
        document.getElementById('opponent_secondary_points').addEventListener('input', saveScoringData);
        document.getElementById('opponent_painting_points').addEventListener('change', saveScoringData);
        
        // Sauvegarde sur changement de type de missions
        document.querySelectorAll('input[name="secondary_type"]').forEach(radio => {
            radio.addEventListener('change', saveData);
        });
        
        // Sauvegarde sur changement de missions fixes
        document.querySelector('select[name="fixed_mission_1"]').addEventListener('change', saveData);
        document.querySelector('select[name="fixed_mission_2"]').addEventListener('change', saveData);
        
        // Effacer les données après soumission
        document.getElementById('submit_btn').addEventListener('click', clearSavedData);
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
        
        // Sauvegarder le type de missions
        saveScoringData();
        
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
        
        // Sauvegarder les missions fixes
        saveScoringData();
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
                    fixedDisplay.innerHTML += createMissionDisplay(mission, true);
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
        saveTacticalStateToDb(); // Sauvegarder en base de données
    }

    // Remettre une mission dans le deck (si la carte le permet) et piocher automatiquement une nouvelle mission
    function shuffleBackMission(missionId) {
        const mission = tacticalState.active.find(m => m.id === missionId);
        if (!mission || !mission.can_shuffle_back) {
            return;
        }

        // Retirer la mission des missions actives (sans la mettre en défaussée/terminée)
        tacticalState.active = tacticalState.active.filter(m => m.id !== missionId);

        // La mission retirée redevient disponible, donc elle ne doit pas être dans usedIds
        const usedIds = [...tacticalState.active, ...tacticalState.discarded, ...tacticalState.completed].map(m => m.id);
        const available = allMissions.filter(m => !usedIds.includes(m.id));

        if (available.length > 0) {
            const randomIndex = Math.floor(Math.random() * available.length);
            const newMission = available[randomIndex];
            tacticalState.active.push(newMission);
        }

        updateTacticalDisplay();
        displaySelectedSecondaryMissions();
        saveData();
        saveTacticalStateToDb();
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
            saveTacticalStateToDb(); // Sauvegarder en base de données
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
            saveTacticalStateToDb(); // Sauvegarder en base de données
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
            saveTacticalStateToDb(); // Sauvegarder en base de données
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
        saveTacticalStateToDb(); // Sauvegarder en base de données
    }

    // Afficher les missions tactiques
    function updateTacticalDisplay() {
        // Missions en jeu
        const activeDiv = document.getElementById('tactical-active-missions');
        let activeHtml = '';
        
        // Afficher les missions actives
        if (tacticalState.active.length > 0) {
            activeHtml += tacticalState.active.map(mission => `
                <div class="bg-white border border-blue-300 rounded p-2 flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-start">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-900">${mission.name_en}</p>
                    </div>
                    <div class="w-full sm:w-auto flex flex-col gap-1 sm:items-end">
                        <div class="grid grid-cols-2 gap-1 w-full sm:flex sm:w-auto">
                            <button type="button" class="w-full sm:w-auto text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded hover:bg-red-200" onclick="discardMission(${mission.id})">
                                Défausser (+1 PC)
                            </button>
                            <button type="button" class="w-full sm:w-auto text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded hover:bg-green-200" onclick="completeMission(${mission.id})">
                                Terminée
                            </button>
                        </div>
                        ${mission.can_shuffle_back ? `
                            <button type="button" class="w-full sm:w-auto text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded hover:bg-amber-200" onclick="shuffleBackMission(${mission.id})">
                                Remettre dans le deck
                            </button>
                        ` : ''}
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


    // Fonctions de validation du formulaire de scoring
    function toggleFinishMatchHint() {
        const selected = document.getElementById('creator_result')?.value;
        const hint = document.getElementById('finish-match-hint');
        if (!hint) return;

        const show = selected === 'creator_abandon'
            || selected === 'opponent_abandon'
            || selected === 'creator_table_rase'
            || selected === 'opponent_table_rase';

        if (show) {
            hint.classList.remove('hidden');
        } else {
            hint.classList.add('hidden');
        }
    }

    function syncCreatorResult() {
        const special = document.querySelector('input[name="creator_result_special"]:checked')?.value;
        const hidden = document.getElementById('creator_result');
        if (!hidden) return;
        hidden.value = special || 'normal';
    }

    function resetSpecialResultSelection() {
        document.querySelectorAll('input[name="creator_result_special"]').forEach(radio => {
            radio.checked = false;
        });
        syncCreatorResult();
        toggleFinishMatchHint();
        try {
            saveData();
        } catch (e) {
            console.error('Erreur resetSpecialResultSelection:', e);
        }
    }

    function updateTotals() {
        // Creator
        const creatorPrimary = parseInt(document.getElementById('creator_primary_points').value) || 0;
        const creatorSecondary = parseInt(document.getElementById('creator_secondary_points').value) || 0;
        const creatorPainting = document.getElementById('creator_painting_points').checked ? 10 : 0;
        const creatorTotal = creatorPrimary + creatorSecondary + creatorPainting;
        document.getElementById('creator_total').textContent = creatorTotal;

        // Opponent
        const opponentPrimary = parseInt(document.getElementById('opponent_primary_points').value) || 0;
        const opponentSecondary = parseInt(document.getElementById('opponent_secondary_points').value) || 0;
        const opponentPainting = document.getElementById('opponent_painting_points').checked ? 10 : 0;
        const opponentTotal = opponentPrimary + opponentSecondary + opponentPainting;
        document.getElementById('opponent_total').textContent = opponentTotal;
    }

    function validateForm() {
        let isValid = true;

        const creatorResultValue = document.getElementById('creator_result')?.value;
        const creatorPrimary = parseInt(document.getElementById('creator_primary_points').value) || 0;
        const creatorSecondary = parseInt(document.getElementById('creator_secondary_points').value) || 0;
        const opponentPrimary = parseInt(document.getElementById('opponent_primary_points').value) || 0;
        const opponentSecondary = parseInt(document.getElementById('opponent_secondary_points').value) || 0;

        // Vérifier que les points sont toujours saisis (obligatoires dans tous les cas)
        // Vérifier creator primaire
        const creatorPrimaryInput = document.getElementById('creator_primary_points');
        const creatorPrimaryError = document.getElementById('creator_primary_error');
        if (creatorPrimary > 50) {
            creatorPrimaryInput.classList.add('border-red-500', 'bg-red-50');
            creatorPrimaryError.classList.remove('hidden');
            isValid = false;
        } else {
            creatorPrimaryInput.classList.remove('border-red-500', 'bg-red-50');
            creatorPrimaryError.classList.add('hidden');
        }

        // Vérifier creator secondaire
        const creatorSecondaryInput = document.getElementById('creator_secondary_points');
        const creatorSecondaryError = document.getElementById('creator_secondary_error');
        if (creatorSecondary > 40) {
            creatorSecondaryInput.classList.add('border-red-500', 'bg-red-50');
            creatorSecondaryError.classList.remove('hidden');
            isValid = false;
        } else {
            creatorSecondaryInput.classList.remove('border-red-500', 'bg-red-50');
            creatorSecondaryError.classList.add('hidden');
        }

        // Vérifier opponent primaire
        const opponentPrimaryInput = document.getElementById('opponent_primary_points');
        const opponentPrimaryError = document.getElementById('opponent_primary_error');
        if (opponentPrimary > 50) {
            opponentPrimaryInput.classList.add('border-red-500', 'bg-red-50');
            opponentPrimaryError.classList.remove('hidden');
            isValid = false;
        } else {
            opponentPrimaryInput.classList.remove('border-red-500', 'bg-red-50');
            opponentPrimaryError.classList.add('hidden');
        }

        // Vérifier opponent secondaire
        const opponentSecondaryInput = document.getElementById('opponent_secondary_points');
        const opponentSecondaryError = document.getElementById('opponent_secondary_error');
        if (opponentSecondary > 40) {
            opponentSecondaryInput.classList.add('border-red-500', 'bg-red-50');
            opponentSecondaryError.classList.remove('hidden');
            isValid = false;
        } else {
            opponentSecondaryInput.classList.remove('border-red-500', 'bg-red-50');
            opponentSecondaryError.classList.add('hidden');
        }

        // LOGIQUE MÉTIER:
        // - Si Abandon/Table rase est coché: valider avec ce choix (points obligatoires mais non utilisés)
        // - Si aucun de ces choix n'est coché: valider uniquement sur les points (points déterminent le gagnant)
        const hasSpecialResult = creatorResultValue === 'opponent_abandon'
            || creatorResultValue === 'opponent_table_rase';

        if (hasSpecialResult) {
            // Si un résultat spécial est coché, c'est valide (points obligatoires mais non utilisés)
            // isValid reste basé sur la validation des points
        } else {
            // Si aucun résultat spécial n'est coché, les points déterminent le gagnant
            // isValid reste basé sur la validation des points
        }

        // Activer/désactiver le bouton de soumission
        // Le bouton est activé si la validation passe (isValid = true)
        const submitBtn = document.getElementById('submit_btn');
        submitBtn.disabled = !isValid;
        
        console.log('Validation:', { 
            isValid, 
            creatorResultSelected: creatorResultValue, 
            creatorPrimary, 
            creatorSecondary, 
            opponentPrimary, 
            opponentSecondary,
            buttonDisabled: submitBtn.disabled
        });
    }

    // ========== SAUVEGARDE EN BASE DE DONNÉES ==========
    let scoringAutoSaveTimeout;
    function saveScoringData() {
        try {
            console.log('saveScoringData() appelée');
            const data = {
                creator_primary_points: document.getElementById('creator_primary_points').value,
                creator_secondary_points: document.getElementById('creator_secondary_points').value,
                creator_painting_points: document.getElementById('creator_painting_points').checked,
                opponent_primary_points: document.getElementById('opponent_primary_points').value,
                opponent_secondary_points: document.getElementById('opponent_secondary_points').value,
                opponent_painting_points: document.getElementById('opponent_painting_points').checked,
                secondary_type: document.querySelector('input[name="secondary_type"]:checked')?.value,
                fixed_mission_1: document.querySelector('select[name="fixed_mission_1"]')?.value || null,
                fixed_mission_2: document.querySelector('select[name="fixed_mission_2"]')?.value || null,
            };
            console.log('Données à sauvegarder:', data);
            
            // Sauvegarder en base de données avec debouncing
            clearTimeout(scoringAutoSaveTimeout);
            scoringAutoSaveTimeout = setTimeout(() => {
                try {
                    const csrfToken = document.querySelector('input[name="_token"]')?.value;
                    if (!csrfToken) {
                        console.error('CSRF token non trouvé');
                        return;
                    }
                    console.log('Envoi des données à l\'API...');
                    fetch(`/api/player-matches/${matchId}/save-draft-scores`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(data),
                    })
                    .then(response => response.json())
                    .then(result => {
                        console.log('Réponse de l\'API:', result);
                        if (result.success) console.log('✓ Scores sauvegardés en base');
                    })
                    .catch(error => console.error('Erreur lors de la sauvegarde:', error));
                } catch (e) {
                    console.error('Erreur:', e);
                }
            }, 2000);
        } catch (e) {
            console.error('Erreur lors de la sauvegarde:', e);
        }
    }

    // Charger les scores depuis la base de données
    function loadSavedData() {
        return fetch(`/api/player-matches/${matchId}/get-draft-scores`)
            .then(response => response.json())
            .then(data => {
                if (data && Object.keys(data).length > 0) {
                    console.log('📥 Scores chargés depuis la base:', data);
                    document.getElementById('creator_primary_points').value = data.creator_primary_points || 0;
                    document.getElementById('creator_secondary_points').value = data.creator_secondary_points || 0;
                    document.getElementById('creator_painting_points').checked = data.creator_painting_points !== false;
                    document.getElementById('opponent_primary_points').value = data.opponent_primary_points || 0;
                    document.getElementById('opponent_secondary_points').value = data.opponent_secondary_points || 0;
                    document.getElementById('opponent_painting_points').checked = data.opponent_painting_points !== false;
                    
                    // Restaurer les missions fixes
                    if (data.fixed_mission_1) {
                        document.querySelector('select[name="fixed_mission_1"]').value = data.fixed_mission_1;
                    }
                    if (data.fixed_mission_2) {
                        document.querySelector('select[name="fixed_mission_2"]').value = data.fixed_mission_2;
                    }
                    
                    updateTotals();
                    validateForm();
                    updateFixedMissions();
                    
                    // Retourner le type de missions pour le traiter après
                    return data.secondary_type;
                }
                return null;
            })
            .catch(error => {
                console.error('Erreur chargement scores:', error);
                return null;
            });
    }

    // Charger SEULEMENT les scores de l'adversaire (pour le polling)
    function loadOpponentScores() {
        return fetch(`/api/player-matches/${matchId}/get-draft-scores`)
            .then(response => response.json())
            .then(data => {
                if (data && Object.keys(data).length > 0) {
                    console.log('📥 Scores adversaire chargés:', data);
                    // Ne recharger que les scores de creator (l'adversaire)
                    document.getElementById('creator_primary_points').value = data.creator_primary_points || 0;
                    document.getElementById('creator_secondary_points').value = data.creator_secondary_points || 0;
                    document.getElementById('creator_painting_points').checked = data.creator_painting_points !== false;
                    
                    updateTotals();
                }
            })
            .catch(error => {
                console.error('Erreur chargement scores adversaire:', error);
            });
    }

    // ========== FONCTIONS POUR LES BOUTONS +/- ==========
    function incrementPoints(fieldId, max) {
        try {
            console.log('incrementPoints appelé:', fieldId, max);
            const input = document.getElementById(fieldId);
            if (!input) {
                console.error('Élément non trouvé:', fieldId);
                return;
            }
            let value = parseInt(input.value) || 0;
            if (value < max) {
                input.value = value + 1;
                console.log('Nouvelle valeur:', input.value);
                updateTotals();
                validateForm();
                saveScoringData();
            }
        } catch (e) {
            console.error('Erreur dans incrementPoints:', e);
        }
    }

    function decrementPoints(fieldId, max) {
        try {
            console.log('decrementPoints appelé:', fieldId, max);
            const input = document.getElementById(fieldId);
            if (!input) {
                console.error('Élément non trouvé:', fieldId);
                return;
            }
            let value = parseInt(input.value) || 0;
            if (value > 0) {
                input.value = value - 1;
                console.log('Nouvelle valeur:', input.value);
                updateTotals();
                validateForm();
                saveScoringData();
            }
        } catch (e) {
            console.error('Erreur dans decrementPoints:', e);
        }
    }

    // ========== SAUVEGARDE EN BASE DE DONNÉES POUR MISSIONS TACTIQUES ==========
    let tacticalAutoSaveTimeout;

    function saveTacticalStateToDb() {
        clearTimeout(tacticalAutoSaveTimeout);
        tacticalAutoSaveTimeout = setTimeout(() => {
            try {
                const csrfToken = document.querySelector('input[name="_token"]')?.value;
                if (!csrfToken) return;
                fetch(`/api/player-matches/${matchId}/save-tactical-state/opponent`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(tacticalState),
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) console.log('Missions tactiques sauvegardées');
                })
                .catch(error => console.error('Erreur:', error));
            } catch (e) {
                console.error('Erreur:', e);
            }
        }, 2000);
    }

    function loadTacticalStateFromDb() {
        return fetch(`/api/player-matches/${matchId}/get-tactical-state/opponent`)
            .then(response => response.json())
            .then(data => {
                if (data.active && data.active.length > 0) {
                    tacticalState = data;
                    console.log('📥 Missions tactiques chargées');
                    updateTacticalDisplay();
                    displaySelectedSecondaryMissions();
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
    }

    // Charger les données au démarrage
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded appelé');
        const validationAlert = document.getElementById('validation-alert');
        const initialValidationAlertHtml = validationAlert ? validationAlert.innerHTML : null;
        if (validationAlert && initialValidationAlertHtml) {
            validationAlert.dataset.initialHtml = initialValidationAlertHtml;
        }
        // Ajouter les listeners pour la sauvegarde IMMÉDIATEMENT
        try {
            document.getElementById('opponent_primary_points').addEventListener('change', saveScoringData);
            document.getElementById('opponent_primary_points').addEventListener('input', saveScoringData);
            document.getElementById('opponent_secondary_points').addEventListener('change', saveScoringData);
            document.getElementById('opponent_secondary_points').addEventListener('input', saveScoringData);
            document.getElementById('opponent_painting_points').addEventListener('change', saveScoringData);
            console.log('Event listeners attachés avec succès');
        } catch (e) {
            console.error('Erreur lors de l\'attachement des listeners:', e);
        }
        
        // Charger les scores et missions tactiques
        loadSavedData().then(secondaryType => {
            loadTacticalStateFromDb().then(() => {
                if (secondaryType) {
                    const typeRadio = document.querySelector(`input[name="secondary_type"][value="${secondaryType}"]`);
                    if (typeRadio) {
                        typeRadio.checked = true;
                        toggleSecondaryType(secondaryType);
                    }
                }
                validateForm();
                toggleFinishMatchHint();
            });
        });
        
        // Polling automatique toutes les 2 secondes pour mettre à jour les scores de l'adversaire
        setInterval(() => {
            loadOpponentScores();
        }, 2000);

        // Polling pour vérifier l'état de validation (toutes les 2 secondes)
        setInterval(() => {
            checkValidationStatus();
        }, 2000);
        
        validateForm();
        toggleFinishMatchHint();

        @if($shouldShowValidationModal)
            if (validationAlert) {
                if (validationAlert.dataset.initialHtml) {
                    validationAlert.innerHTML = validationAlert.dataset.initialHtml;
                }
                validationAlert.classList.remove('hidden');
            }
        @endif
    });

    // Flags pour tracker l'état de validation
    let rejectionAlertShown = false;
    let previousValidationState = { creator: false, opponent: false };
    let firstPoll = true;

    // Fonction pour vérifier l'état de validation
    function checkValidationStatus() {
        fetch(`/api/player-matches/${matchId}/validation-status`)
            .then(response => response.json())
            .then(data => {
                const validationAlert = document.getElementById('validation-alert');
                const waitingAlert = document.getElementById('waiting-alert');
                const validateBtn = document.getElementById('validate-opponent-btn');
                
                // Au premier appel, initialiser l'état précédent
                if (firstPoll) {
                    previousValidationState = { creator: data.creator_validated, opponent: data.opponent_validated };
                    firstPoll = false;
                    console.log('Premier poll - État initial:', previousValidationState);
                    // Ne pas retourner ici: si l'utilisateur arrive après la validation de l'autre joueur,
                    // on doit afficher la modal dès le premier poll.
                }
                
                // Déterminer si c'est un refus (les deux étaient validés, maintenant reset)
                const wasValidated = previousValidationState.creator || previousValidationState.opponent;
                const nowReset = !data.creator_validated && !data.opponent_validated;
                
                // Si les validations ont été réinitialisées (refus)
                if (wasValidated && nowReset && data.status === 'confirmed') {
                    console.log('❌ Validation refusée par l\'adversaire');
                    if (validationAlert) {
                        validationAlert.classList.add('hidden');
                    }
                    if (waitingAlert) {
                        waitingAlert.classList.add('hidden');
                    }

                    // Réinitialiser les options (abandon/table rase) après refus
                    resetSpecialResultSelection();

                    // Afficher un message d'alerte une seule fois
                    if (!rejectionAlertShown) {
                        rejectionAlertShown = true;
                        alert('Validation refusée. Vous pouvez corriger les scores.');
                    }
                    previousValidationState = { creator: data.creator_validated, opponent: data.opponent_validated };
                    return;
                }
                
                // Réinitialiser le flag si les validations reprennent
                if (data.creator_validated || data.opponent_validated) {
                    rejectionAlertShown = false;
                }
                
                // Mettre à jour l'état précédent
                previousValidationState = { creator: data.creator_validated, opponent: data.opponent_validated };
                
                // Si le créateur a validé et que nous n'avons pas encore validé
                if (data.creator_validated && !data.opponent_validated && validationAlert) {
                    if (validationAlert.dataset.initialHtml) {
                        validationAlert.innerHTML = validationAlert.dataset.initialHtml;
                    }
                    validationAlert.classList.remove('hidden');
                    console.log('✅ Le créateur a validé le score!');
                }
                
                // Si les deux ont validé, le match est complété
                if (data.creator_validated && data.opponent_validated && data.status === 'completed') {
                    console.log('✅ Match finalisé!');
                    if (validationAlert) {
                        validationAlert.classList.add('hidden');
                    }
                    if (waitingAlert) {
                        waitingAlert.classList.remove('hidden');
                        waitingAlert.innerHTML = `
                            <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4">
                                <p class="text-green-900 font-semibold">✅ Match finalisé!</p>
                                <p class="text-green-700 text-sm">Les deux joueurs ont validé le résultat.</p>
                            </div>
                        `;
                    }
                    setTimeout(() => {
                        window.location.href = '{{ route("player-matches.index") }}';
                    }, 2000);
                }
            })
            .catch(error => console.error('Erreur vérification validation:', error));
    }

    // Fonction pour valider le score du créateur
    function validateOpponentScore() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            console.error('CSRF token non trouvé');
            return;
        }

        fetch(`{{ route('player-matches.validate-opponent-score', $playerMatch) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({}),
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                console.log('✅ Score validé:', result.message);
                const validationAlert = document.getElementById('validation-alert');
                const waitingAlert = document.getElementById('waiting-alert');
                if (validationAlert) {
                    validationAlert.classList.add('hidden');
                }
                if (waitingAlert) {
                    waitingAlert.classList.remove('hidden');
                    waitingAlert.innerHTML = `
                        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4">
                            <p class="text-green-900 font-semibold">✅ ${result.message}</p>
                        </div>
                    `;
                }
                // Rediriger vers la page des matchs après 2 secondes
                setTimeout(() => {
                    window.location.href = '{{ route("player-matches.index") }}';
                }, 2000);
            }
        })
        .catch(error => console.error('Erreur validation:', error));
    }

    // Fonction pour soumettre le formulaire de score
    function submitScoreForm(event) {
        event.preventDefault();
        
        const form = document.getElementById('score-form');
        const formData = new FormData(form);
        const csrfToken = document.querySelector('input[name="_token"]')?.value;
        
        // Convertir FormData en objet JSON
        const data = {
            creator_result: formData.get('creator_result'),
            creator_primary_points: parseInt(formData.get('creator_primary_points')) || 0,
            creator_secondary_points: parseInt(formData.get('creator_secondary_points')) || 0,
            creator_painting_points: formData.get('creator_painting_points') === 'on',
            opponent_primary_points: parseInt(formData.get('opponent_primary_points')) || 0,
            opponent_secondary_points: parseInt(formData.get('opponent_secondary_points')) || 0,
            opponent_painting_points: formData.get('opponent_painting_points') === 'on',
        };

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                console.log('✅ Score enregistré:', result.message);

                const validationAlert = document.getElementById('validation-alert');
                const waitingAlert = document.getElementById('waiting-alert');
                if (validationAlert) {
                    validationAlert.classList.add('hidden');
                }

                if (waitingAlert) {
                    waitingAlert.classList.remove('hidden');

                    if (result.status === 'completed') {
                        waitingAlert.innerHTML = `
                            <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4">
                                <p class="text-green-900 font-semibold">✅ Match finalisé!</p>
                                <p class="text-green-700 text-sm">Les deux joueurs ont validé le résultat.</p>
                            </div>
                        `;
                        setTimeout(() => {
                            window.location.href = '{{ route("player-matches.index") }}';
                        }, 2000);
                    } else {
                        waitingAlert.innerHTML = `
                            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4">
                                <p class="text-blue-900 font-semibold">⏳ ${result.message}</p>
                                <p class="text-blue-700 text-sm mt-1">Vous pouvez fermer cette page, vous serez notifié quand l'adversaire validera.</p>
                            </div>
                        `;
                    }
                }
            } else {
                console.error('❌ Erreur:', result.error);
                alert('Erreur: ' + (result.error || 'Impossible d\'enregistrer le score'));
            }
        })
        .catch(error => {
            console.error('Erreur réseau:', error);
            alert('Erreur réseau: ' + error.message);
        });
    }

    // Fonction pour refuser la validation
    function rejectValidation() {
        const csrfToken = document.querySelector('input[name="_token"]')?.value;
        if (!csrfToken) {
            console.error('CSRF token non trouvé');
            return;
        }

        fetch(`{{ route('player-matches.reject-score-validation', $playerMatch) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({}),
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                console.log('❌ Validation refusée:', result.message);
                const validationAlert = document.getElementById('validation-alert');
                if (validationAlert) {
                    validationAlert.classList.add('hidden');
                }
                // Afficher un message de confirmation
                alert('Validation refusée. Vous pouvez corriger les scores.');
            } else {
                console.error('❌ Erreur:', result.error);
                alert('Erreur: ' + (result.error || 'Impossible de refuser la validation'));
            }
        })
        .catch(error => {
            console.error('Erreur réseau:', error);
            alert('Erreur réseau: ' + error.message);
        });
    }
</script>
@endsection
