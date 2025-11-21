@extends('layouts.public')

@section('title', 'Mode Spectateur - Match Tournoi')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold flex items-center gap-2">
                        👁️ Mode Spectateur
                    </h1>
                    <p class="text-red-100 mt-2">
                        {{ $tournament->name }} - {{ $match->player1->name }} vs {{ $match->player2->name }}
                    </p>
                </div>
                <a href="{{ route('tournaments.matches.show', [$tournament, $match]) }}" 
                   class="bg-white text-red-600 px-4 py-2 rounded-lg font-semibold hover:bg-red-50">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Scores en temps réel -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8" id="scores-container">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">🎯 Scores en Temps Réel</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Joueur 1 -->
                <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                    <h3 class="font-bold text-red-900 mb-3">{{ $match->player1->name }}</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Total:</span>
                            <span class="font-bold text-lg" id="player1-total">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Primaire:</span>
                            <span id="player1-primary">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Secondaire:</span>
                            <span id="player1-secondary">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Peinture:</span>
                            <span id="player1-painting">-</span>
                        </div>
                    </div>
                </div>

                <!-- Joueur 2 -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4">
                    <h3 class="font-bold text-blue-900 mb-3">{{ $match->player2->name }}</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Total:</span>
                            <span class="font-bold text-lg" id="player2-total">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Primaire:</span>
                            <span id="player2-primary">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Secondaire:</span>
                            <span id="player2-secondary">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Peinture:</span>
                            <span id="player2-painting">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-4">
                ⏱️ Mise à jour automatique toutes les 2 secondes (décalage 2s)
            </p>
        </div>

        <!-- Missions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Mission Primaire -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">🎲 Mission Primaire</h2>
                @if($match->primaryMission)
                    <h3 class="font-bold text-red-600 mb-2">{{ $match->primaryMission->name_fr ?? $match->primaryMission->name }}</h3>
                    <p class="text-gray-700 text-sm mb-3">{{ $match->primaryMission->description_fr ?? $match->primaryMission->description }}</p>
                    <div class="bg-gray-50 p-3 rounded max-h-64 overflow-y-auto text-sm text-gray-600">
                        {!! nl2br(e($match->primaryMission->full_text_fr ?? $match->primaryMission->full_text)) !!}
                    </div>
                @else
                    <p class="text-gray-500">Aucune mission primaire</p>
                @endif
            </div>

            <!-- Péripétie -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">🌪️ Péripétie</h2>
                @if($match->twistMission)
                    <h3 class="font-bold text-purple-600 mb-2">{{ $match->twistMission->name_fr ?? $match->twistMission->name }}</h3>
                    <p class="text-gray-700 text-sm mb-3">{{ $match->twistMission->description_fr ?? $match->twistMission->description }}</p>
                    <div class="bg-gray-50 p-3 rounded max-h-64 overflow-y-auto text-sm text-gray-600">
                        {!! nl2br(e($match->twistMission->full_text_fr ?? $match->twistMission->full_text)) !!}
                    </div>
                @else
                    <p class="text-gray-500">Aucune péripétie</p>
                @endif
            </div>
        </div>

        <!-- Déploiement et Terrain -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Déploiement -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">🎯 Déploiement</h2>
                <p class="text-lg font-bold text-gray-900 mb-3">
                    {{ ucfirst(str_replace('_', ' ', $match->deployment_mode ?? 'Normal')) }}
                </p>
                @php
                    $deploymentCard = \App\Models\StrikeForceDeploymentCard::where('name', 'LIKE', '%' . str_replace(' ', '%', $match->deployment_mode) . '%')->first();
                @endphp
                @if($deploymentCard && $deploymentCard->image_path)
                    <img src="{{ asset('storage/' . $deploymentCard->image_path) }}" 
                         alt="{{ $deploymentCard->name }}"
                         class="w-full h-48 object-contain rounded">
                @else
                    <div class="bg-gray-100 rounded h-48 flex items-center justify-center">
                        <p class="text-gray-500">Image non disponible</p>
                    </div>
                @endif
            </div>

            <!-- Disposition Terrain -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">🗺️ Disposition Terrain</h2>
                @if($match->terrainLayout)
                    <p class="text-lg font-bold text-gray-900 mb-3">{{ $match->terrainLayout->name_fr ?? $match->terrainLayout->name }}</p>
                    @if($match->terrainLayout->image_path)
                        <img src="{{ asset('storage/' . $match->terrainLayout->image_path) }}" 
                             alt="{{ $match->terrainLayout->name_fr ?? $match->terrainLayout->name }}"
                             class="w-full h-48 object-contain rounded">
                    @else
                        <div class="bg-gray-100 rounded h-48 flex items-center justify-center">
                            <p class="text-gray-500">Image non disponible</p>
                        </div>
                    @endif
                @else
                    <p class="text-gray-500">Aucune disposition terrain</p>
                @endif
            </div>
        </div>

    </div>
</div>

<script>
    const apiUrl = '{{ $apiUrl }}';
    let pollInterval;

    // Charger les scores au démarrage
    loadScores();

    // Polling toutes les 2 secondes
    pollInterval = setInterval(loadScores, 2000);

    async function loadScores() {
        try {
            const response = await fetch(apiUrl);
            const data = await response.json();

            if (data.error) {
                console.error('Erreur:', data.error);
                return;
            }

            // Calculer le délai
            const now = new Date();
            const delayUntil = new Date(data.delay_until);
            const wait = Math.max(0, delayUntil - now);

            // Attendre avant d'afficher
            setTimeout(() => {
                updateScores(data);
            }, wait);
        } catch (error) {
            console.error('Erreur lors du chargement des scores:', error);
        }
    }

    function updateScores(data) {
        // Scores joueur 1 - Convertir en nombres
        const player1Primary = parseInt(data.player1_primary_points) || 0;
        const player1Secondary = parseInt(data.player1_secondary_points) || 0;
        const player1Painting = data.player1_painting_points ? 10 : 0;
        const player1Total = player1Primary + player1Secondary + player1Painting;
        
        document.getElementById('player1-total').textContent = player1Total;
        document.getElementById('player1-primary').textContent = player1Primary;
        document.getElementById('player1-secondary').textContent = player1Secondary;
        document.getElementById('player1-painting').textContent = data.player1_painting_points ? '+10' : '-';

        // Scores joueur 2 - Convertir en nombres
        const player2Primary = parseInt(data.player2_primary_points) || 0;
        const player2Secondary = parseInt(data.player2_secondary_points) || 0;
        const player2Painting = data.player2_painting_points ? 10 : 0;
        const player2Total = player2Primary + player2Secondary + player2Painting;
        
        document.getElementById('player2-total').textContent = player2Total;
        document.getElementById('player2-primary').textContent = player2Primary;
        document.getElementById('player2-secondary').textContent = player2Secondary;
        document.getElementById('player2-painting').textContent = data.player2_painting_points ? '+10' : '-';

        // Missions tactiques
        const tacticalPlayer1 = data.draft_tactical_state_player1 || {};
        document.getElementById('tactical-hand').textContent = (tacticalPlayer1.hand || []).length;
        document.getElementById('tactical-discarded').textContent = (tacticalPlayer1.discarded || []).length;
        document.getElementById('tactical-completed').textContent = (tacticalPlayer1.completed || []).length;
    }

    // Arrêter le polling si l'utilisateur quitte la page
    window.addEventListener('beforeunload', () => {
        clearInterval(pollInterval);
    });
</script>
@endsection
