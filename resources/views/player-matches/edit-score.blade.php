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
                
                <!-- Résultat du match -->
                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-gray-900 mb-4">Résultat du match *</label>
                    
                    <!-- Option Nul centrée -->
                    <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer max-w-md mx-auto">
                        <input type="radio" name="creator_result" value="nul" onchange="updateOpponentOptions(); updateTotals()" class="mr-3 mt-0.5 flex-shrink-0">
                        <div>
                            <span class="text-sm font-semibold text-gray-900">Nul</span>
                            <p class="text-xs text-gray-500">Les deux joueurs font match nul</p>
                        </div>
                    </label>

                    <!-- Deux colonnes pour Abandon et Table rase -->
                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <!-- Colonne 1 -->
                        <div class="space-y-3">
                            <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="creator_result" value="creator_abandon" onchange="updateOpponentOptions(); updateTotals()" class="mr-3 mt-0.5 flex-shrink-0">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900">Abandon</span>
                                    <p class="text-xs text-gray-500">{{ $playerMatch->creator->name }} abandonne</p>
                                </div>
                            </label>
                            <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="creator_result" value="creator_table_rase" onchange="updateOpponentOptions(); updateTotals()" class="mr-3 mt-0.5 flex-shrink-0">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900">Table rase</span>
                                    <p class="text-xs text-gray-500">{{ $playerMatch->creator->name }} est table rase</p>
                                </div>
                            </label>
                        </div>
                        <!-- Colonne 2 -->
                        <div class="space-y-3">
                            <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="creator_result" value="opponent_abandon" onchange="updateOpponentOptions(); updateTotals()" class="mr-3 mt-0.5 flex-shrink-0">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900">Abandon</span>
                                    <p class="text-xs text-gray-500">{{ $playerMatch->opponent->name }} abandonne</p>
                                </div>
                            </label>
                            <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="creator_result" value="opponent_table_rase" onchange="updateOpponentOptions(); updateTotals()" class="mr-3 mt-0.5 flex-shrink-0">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900">Table rase</span>
                                    <p class="text-xs text-gray-500">{{ $playerMatch->opponent->name }} est table rase</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <input type="hidden" name="opponent_result" id="opponent_result" value="nul">
                </div>

                <hr class="my-6">

                <div class="grid grid-cols-2 gap-6">
                    <!-- Créateur -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $playerMatch->creator->name }}</h3>
                        
                        <div>
                            <label for="creator_primary_points" class="block text-sm font-semibold text-gray-900 mb-2">Points Mission Primaire (max 50) *</label>
                            <input type="number" id="creator_primary_points" name="creator_primary_points" min="0" value="{{ old('creator_primary_points', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors" required onchange="updateTotals(); validateForm()" oninput="updateTotals(); validateForm()">
                            <p id="creator_primary_error" class="text-xs text-red-600 mt-1 hidden">Maximum 50 points</p>
                        </div>

                        <div>
                            <label for="creator_secondary_points" class="block text-sm font-semibold text-gray-900 mb-2">Points Mission Secondaire (max 40) *</label>
                            <input type="number" id="creator_secondary_points" name="creator_secondary_points" min="0" value="{{ old('creator_secondary_points', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors" required onchange="updateTotals(); validateForm()" oninput="updateTotals(); validateForm()">
                            <p id="creator_secondary_error" class="text-xs text-red-600 mt-1 hidden">Maximum 40 points</p>
                        </div>

                        <label class="flex items-center">
                            <input type="checkbox" id="creator_painting_points" name="creator_painting_points" value="1" checked class="mr-2" onchange="updateTotals()">
                            <span class="text-sm font-semibold text-gray-900">Points de peinture (+10 points)</span>
                        </label>

                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-600">Total: <span id="creator_total" class="font-semibold text-gray-900">10</span> points</p>
                        </div>
                    </div>

                    <!-- Adversaire -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $playerMatch->opponent->name }}</h3>
                        
                        <div>
                            <label for="opponent_primary_points" class="block text-sm font-semibold text-gray-900 mb-2">Points Mission Primaire (max 50) *</label>
                            <input type="number" id="opponent_primary_points" name="opponent_primary_points" min="0" value="{{ old('opponent_primary_points', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors" required onchange="updateTotals(); validateForm()" oninput="updateTotals(); validateForm()">
                            <p id="opponent_primary_error" class="text-xs text-red-600 mt-1 hidden">Maximum 50 points</p>
                        </div>

                        <div>
                            <label for="opponent_secondary_points" class="block text-sm font-semibold text-gray-900 mb-2">Points Mission Secondaire (max 40) *</label>
                            <input type="number" id="opponent_secondary_points" name="opponent_secondary_points" min="0" value="{{ old('opponent_secondary_points', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors" required onchange="updateTotals(); validateForm()" oninput="updateTotals(); validateForm()">
                            <p id="opponent_secondary_error" class="text-xs text-red-600 mt-1 hidden">Maximum 40 points</p>
                        </div>

                        <label class="flex items-center">
                            <input type="checkbox" id="opponent_painting_points" name="opponent_painting_points" value="1" checked class="mr-2" onchange="updateTotals()">
                            <span class="text-sm font-semibold text-gray-900">Points de peinture (+10 points)</span>
                        </label>

                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-600">Total: <span id="opponent_total" class="font-semibold text-gray-900">10</span> points</p>
                        </div>
                    </div>
                </div>

                <!-- Champs cachés pour les points de victoire -->
                <input type="hidden" name="creator_victory_points" id="creator_victory_points" value="0">
                <input type="hidden" name="opponent_victory_points" id="opponent_victory_points" value="0">
                <div class="flex gap-4">
                    <button type="submit" id="submit_btn" class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
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
                <li>• Points Mission Primaire : maximum 50 points</li>
                <li>• Points Mission Secondaire : maximum 40 points</li>
                <li>• Points de peinture : 10 points (coché par défaut)</li>
                <li>• Le total s'affiche automatiquement en bas de chaque colonne</li>
            </ul>
        </div>
    </div>
</div>

<script>
function updateOpponentOptions() {
    const creatorResult = document.querySelector('input[name="creator_result"]:checked').value;
    let opponentResult = creatorResult;
    
    // Mapper les résultats du créateur aux résultats de l'adversaire
    if (creatorResult === 'creator_abandon') {
        opponentResult = 'abandon';
    } else if (creatorResult === 'opponent_abandon') {
        opponentResult = 'victoire';
    } else if (creatorResult === 'creator_table_rase') {
        opponentResult = 'table_rase';
    } else if (creatorResult === 'opponent_table_rase') {
        opponentResult = 'victoire';
    }
    
    document.getElementById('opponent_result').value = opponentResult;
}

function updateTotals() {
    // Créateur
    const creatorPrimary = parseInt(document.getElementById('creator_primary_points').value) || 0;
    const creatorSecondary = parseInt(document.getElementById('creator_secondary_points').value) || 0;
    const creatorPainting = document.getElementById('creator_painting_points').checked ? 10 : 0;
    const creatorTotal = creatorPrimary + creatorSecondary + creatorPainting;
    document.getElementById('creator_total').textContent = creatorTotal;

    // Adversaire
    const opponentPrimary = parseInt(document.getElementById('opponent_primary_points').value) || 0;
    const opponentSecondary = parseInt(document.getElementById('opponent_secondary_points').value) || 0;
    const opponentPainting = document.getElementById('opponent_painting_points').checked ? 10 : 0;
    const opponentTotal = opponentPrimary + opponentSecondary + opponentPainting;
    document.getElementById('opponent_total').textContent = opponentTotal;
}

function validateForm() {
    let isValid = true;

    // VÉRIFICATION PRIORITAIRE: Vérifier que le résultat du match est sélectionné
    const creatorResultSelected = document.querySelector('input[name="creator_result"]:checked');
    if (!creatorResultSelected) {
        isValid = false;
    }

    const creatorPrimary = parseInt(document.getElementById('creator_primary_points').value) || 0;
    const creatorSecondary = parseInt(document.getElementById('creator_secondary_points').value) || 0;
    const opponentPrimary = parseInt(document.getElementById('opponent_primary_points').value) || 0;
    const opponentSecondary = parseInt(document.getElementById('opponent_secondary_points').value) || 0;

    // Vérifier créateur primaire
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

    // Vérifier créateur secondaire
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

    // Vérifier adversaire primaire
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

    // Vérifier adversaire secondaire
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

    // Activer/désactiver le bouton de soumission
    const submitBtn = document.getElementById('submit_btn');
    submitBtn.disabled = !isValid;
}

// Initialiser les totaux et la validation au chargement
document.addEventListener('DOMContentLoaded', function() {
    updateTotals();
    validateForm();
});
</script>
@endsection
