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
                            Enregistrer le score
                        </h2>
                        <p class="text-red-100 mt-1">
                            Match entre {{ $playerMatch->creator->name }} et {{ $playerMatch->opponent->name }}
                        </p>
                    </div>
                    <a href="{{ route('player-matches.show', $playerMatch) }}" 
                       class="text-white hover:text-red-100 font-semibold">
                        ← Annuler
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <form action="{{ route('player-matches.set-score', $playerMatch) }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-2 gap-6">
                    <!-- Créateur -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">{{ $playerMatch->creator->name }}</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="creator_result" value="victoire" checked onchange="updateOpponentOptions()" class="mr-2">
                                    <span class="text-sm">Victoire</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="creator_result" value="defaite" onchange="updateOpponentOptions()" class="mr-2">
                                    <span class="text-sm">Défaite</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="creator_result" value="abandon" onchange="updateOpponentOptions()" class="mr-2">
                                    <span class="text-sm">Abandon</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="creator_result" value="table_rase" onchange="updateOpponentOptions()" class="mr-2">
                                    <span class="text-sm">Table rase</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="creator_result" value="nul" onchange="updateOpponentOptions()" class="mr-2">
                                    <span class="text-sm">Nul</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="creator_victory_points" class="block text-sm font-semibold text-gray-900 mb-2">Points de victoire *</label>
                            <input type="number" id="creator_victory_points" name="creator_victory_points" min="0" value="{{ old('creator_victory_points') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                        </div>
                    </div>

                    <!-- Adversaire -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">{{ $playerMatch->opponent->name }}</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="opponent_result" value="victoire" checked onchange="updateCreatorOptions()" class="mr-2">
                                    <span class="text-sm">Victoire</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="opponent_result" value="defaite" onchange="updateCreatorOptions()" class="mr-2">
                                    <span class="text-sm">Défaite</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="opponent_result" value="abandon" onchange="updateCreatorOptions()" class="mr-2">
                                    <span class="text-sm">Abandon</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="opponent_result" value="table_rase" onchange="updateCreatorOptions()" class="mr-2">
                                    <span class="text-sm">Table rase</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="opponent_result" value="nul" onchange="updateCreatorOptions()" class="mr-2">
                                    <span class="text-sm">Nul</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="opponent_victory_points" class="block text-sm font-semibold text-gray-900 mb-2">Points de victoire *</label>
                            <input type="number" id="opponent_victory_points" name="opponent_victory_points" min="0" value="{{ old('opponent_victory_points') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                        </div>
                    </div>
                </div>
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                        Enregistrer le score
                    </button>
                    <a href="{{ route('player-matches.show', $playerMatch) }}" class="flex-1 text-center bg-gray-200 text-gray-900 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Aide -->
        <div class="mt-6 bg-red-50 border-2 border-red-200 rounded-lg p-4">
            <h4 class="font-semibold text-red-900 mb-2">💡 Aide</h4>
            <ul class="text-sm text-red-800 space-y-1">
                <li>• Les scores sont calculés automatiquement : Victoire = 3 pts, Nul = 1 pt, Défaite/Abandon/Table rase = 0 pt</li>
                <li>• Les points de victoire sont obligatoires pour les départages</li>
            </ul>
        </div>
    </div>
</div>

<script>
function updateCreatorOptions() {
    const opponentResult = document.querySelector('input[name="opponent_result"]:checked').value;
    const creatorRadios = document.querySelectorAll('input[name="creator_result"]');
    
    let forcedResult = null;
    if (opponentResult === 'victoire') {
        forcedResult = 'defaite';
    } else if (opponentResult === 'defaite') {
        forcedResult = 'victoire';
    } else if (opponentResult === 'abandon') {
        forcedResult = 'victoire';
    } else if (opponentResult === 'table_rase') {
        forcedResult = 'victoire';
    } else if (opponentResult === 'nul') {
        forcedResult = 'nul';
    }
    
    if (forcedResult) {
        creatorRadios.forEach(radio => {
            if (radio.value === forcedResult) {
                radio.checked = true;
            }
        });
    }
}

function updateOpponentOptions() {
    const creatorResult = document.querySelector('input[name="creator_result"]:checked').value;
    const opponentRadios = document.querySelectorAll('input[name="opponent_result"]');
    
    let forcedResult = null;
    if (creatorResult === 'victoire') {
        forcedResult = 'defaite';
    } else if (creatorResult === 'defaite') {
        forcedResult = 'victoire';
    } else if (creatorResult === 'abandon') {
        forcedResult = 'victoire';
    } else if (creatorResult === 'table_rase') {
        forcedResult = 'victoire';
    } else if (creatorResult === 'nul') {
        forcedResult = 'nul';
    }
    
    if (forcedResult) {
        opponentRadios.forEach(radio => {
            if (radio.value === forcedResult) {
                radio.checked = true;
            }
        });
    }
}
</script>
@endsection
