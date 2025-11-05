@extends('layouts.public')

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- En-tête -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 overflow-hidden shadow-md sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-white">
                                Saisir le résultat
                            </h2>
                            <p class="text-red-100 mt-1">
                                Round {{ $match->round }}
                                @if($match->table_number)
                                    - Table {{ $match->table_number }}
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('tournaments.matches.show', [$tournament, $match]) }}" 
                           class="text-white hover:text-red-100 font-semibold">
                            ← Annuler
                        </a>
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <form method="POST" action="{{ route('tournaments.matches.update', [$tournament, $match]) }}">
                @csrf
                @method('PUT')

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <!-- Joueur 1 -->
                        <div class="border-2 border-red-200 rounded-lg p-6 bg-red-50">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">
                                {{ $match->player1->name }}
                            </h3>
                            @if($match->player1ArmyList)
                                <p class="text-gray-600 mb-4">
                                    {{ $match->player1ArmyList->faction ? $match->player1ArmyList->faction->name : 'Faction non spécifiée' }}
                                    @if($match->player1ArmyList->detachment)
                                        - {{ $match->player1ArmyList->detachment }}
                                    @endif
                                </p>
                            @endif

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Résultat *</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="player1_result" value="victoire" checked onchange="updatePlayer2Options()" class="mr-2">
                                            <span class="text-sm">Victoire</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="player1_result" value="defaite" onchange="updatePlayer2Options()" class="mr-2">
                                            <span class="text-sm">Défaite</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="player1_result" value="abandon" onchange="updatePlayer2Options()" class="mr-2">
                                            <span class="text-sm">Abandon</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="player1_result" value="table_rase" onchange="updatePlayer2Options()" class="mr-2">
                                            <span class="text-sm">Table rase</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="player1_result" value="nul" onchange="updatePlayer2Options()" class="mr-2">
                                            <span class="text-sm">Nul</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label for="player1_victory_points" class="block text-sm font-medium text-gray-700 mb-2">
                                        Points de victoire *
                                    </label>
                                    <input type="number" 
                                           name="player1_victory_points" 
                                           id="player1_victory_points" 
                                           min="0"
                                           value="{{ old('player1_victory_points', $match->player1_victory_points) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                           required>
                                    @error('player1_victory_points')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!--  -->
                        <div class="text-center text-2xl font-bold text-gray-500"></div>

                        <!-- Joueur 2 -->
                        <div class="border-2 border-red-200 rounded-lg p-6 bg-red-50">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">
                                {{ $match->player2->name }}
                            </h3>
                            @if($match->player2ArmyList)
                                <p class="text-gray-600 mb-4">
                                    {{ $match->player2ArmyList->faction ? $match->player2ArmyList->faction->name : 'Faction non spécifiée' }}
                                    @if($match->player2ArmyList->detachment)
                                        - {{ $match->player2ArmyList->detachment }}
                                    @endif
                                </p>
                            @endif

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Résultat *</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="player2_result" value="victoire" checked onchange="updatePlayer1Options()" class="mr-2">
                                            <span class="text-sm">Victoire</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="player2_result" value="defaite" onchange="updatePlayer1Options()" class="mr-2">
                                            <span class="text-sm">Défaite</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="player2_result" value="abandon" onchange="updatePlayer1Options()" class="mr-2">
                                            <span class="text-sm">Abandon</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="player2_result" value="table_rase" onchange="updatePlayer1Options()" class="mr-2">
                                            <span class="text-sm">Table rase</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="player2_result" value="nul" onchange="updatePlayer1Options()" class="mr-2">
                                            <span class="text-sm">Nul</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label for="player2_victory_points" class="block text-sm font-medium text-gray-700 mb-2">
                                        Points de victoire *
                                    </label>
                                    <input type="number" 
                                           name="player2_victory_points" 
                                           id="player2_victory_points" 
                                           min="0"
                                           value="{{ old('player2_victory_points', $match->player2_victory_points) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                           required>
                                    @error('player2_victory_points')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes (optionnel)
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="4"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                      placeholder="Remarques, incidents, etc.">{{ old('notes', $match->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Boutons -->
                        <div class="flex gap-4">
                            <button type="submit" 
                                    class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition text-sm">
                                Enregistrer le résultat
                            </button>
                            <a href="{{ route('tournaments.matches.show', [$tournament, $match]) }}" 
                               class="flex-1 text-center px-4 py-2 bg-gray-400 text-white font-medium rounded-lg hover:bg-gray-500 transition text-sm">
                                Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Aide -->
            <div class="mt-6 bg-red-50 border-2 border-red-200 rounded-lg p-4">
                <h4 class="font-semibold text-red-900 mb-2">💡 Aide</h4>
                <ul class="text-sm text-red-800 space-y-1">
                    <li>• Le vainqueur sera déterminé automatiquement en fonction des scores</li>
                    <li>• Sélectionnez "Nul" si les deux joueurs ont le même score</li>
                    <li>• Les scores sont calculés automatiquement : Victoire = 3 pts, Nul = 1 pt, Défaite/Abandon/Table rase = 0 pt</li>
                    <li>• Les points de victoire sont obligatoires pour les départages</li>
                </ul>
            </div>
        </div>
    </div>

<script>
function updatePlayer1Options() {
    const player2Result = document.querySelector('input[name="player2_result"]:checked').value;
    const player1Radios = document.querySelectorAll('input[name="player1_result"]');
    
    // Déterminer le résultat forcé pour le joueur 1
    let forcedResult = null;
    if (player2Result === 'victoire') {
        forcedResult = 'defaite';
    } else if (player2Result === 'defaite') {
        forcedResult = 'victoire';
    } else if (player2Result === 'abandon') {
        forcedResult = 'victoire';
    } else if (player2Result === 'table_rase') {
        forcedResult = 'victoire';
    } else if (player2Result === 'nul') {
        forcedResult = 'nul';
    }
    
    // Forcer le résultat
    if (forcedResult) {
        player1Radios.forEach(radio => {
            if (radio.value === forcedResult) {
                radio.checked = true;
            }
        });
    }
}

function updatePlayer2Options() {
    const player1Result = document.querySelector('input[name="player1_result"]:checked').value;
    const player2Radios = document.querySelectorAll('input[name="player2_result"]');
    
    // Déterminer le résultat forcé pour le joueur 2
    let forcedResult = null;
    if (player1Result === 'victoire') {
        forcedResult = 'defaite';
    } else if (player1Result === 'defaite') {
        forcedResult = 'victoire';
    } else if (player1Result === 'abandon') {
        forcedResult = 'victoire';
    } else if (player1Result === 'table_rase') {
        forcedResult = 'victoire';
    } else if (player1Result === 'nul') {
        forcedResult = 'nul';
    }
    
    // Forcer le résultat
    if (forcedResult) {
        player2Radios.forEach(radio => {
            if (radio.value === forcedResult) {
                radio.checked = true;
            }
        });
    }
}
</script>
</div>
</div>
@endsection
