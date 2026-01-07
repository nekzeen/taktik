@extends('layouts.public')

@section('title', 'Résumé de la configuration')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- En-tête gradient rouge -->
    <div class="bg-gradient-to-r from-red-600 to-red-700 border-b border-red-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Résumé de la configuration</h1>
                    @if($matchType === 'player' && $match->is_setup_validated)
                        <p class="text-green-100">Configuration validée et verrouillée</p>
                    @else
                        <p class="text-red-100">Configuration du match sauvegardée avec succès</p>
                    @endif
                </div>
                <a href="{{ $matchType === 'tournament' ? route('tournaments.matches.index', $match->tournament_id) : route('player-matches.show', $match->id) }}" class="px-4 py-2 bg-white text-red-600 rounded-lg font-semibold hover:bg-red-50 transition">
                    Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Grille responsive -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Zone de déploiement -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Zone de déploiement</h2>
                    
                    @if($match->deployment_mode)
                        <div class="space-y-4">
                            <p class="text-2xl font-bold text-red-600">{{ $match->deployment_mode }}</p>
                            @php
                                $deploymentCard = \App\Models\StrikeForceDeploymentCard::where('name', 'LIKE', '%' . str_replace(' ', '%', $match->deployment_mode) . '%')->first();
                            @endphp
                            @if($deploymentCard && $deploymentCard->image_path)
                                <img src="{{ asset('storage/' . $deploymentCard->image_path) }}" alt="{{ $deploymentCard->name }}" class="w-full h-auto block rounded-lg">
                            @endif
                        </div>
                    @else
                        <p class="text-gray-500">Non configurée</p>
                    @endif
                </div>
            </div>

            <!-- Disposition de terrain avec image -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Disposition de terrain</h2>
                    
                    @if($match->terrainLayout)
                        <div class="space-y-4">
                            <p class="text-2xl font-bold text-blue-600">{{ $match->terrainLayout->name }}</p>
                            @if($match->terrainLayout->image_path)
                                <img src="{{ asset('storage/' . $match->terrainLayout->image_path) }}" alt="{{ $match->terrainLayout->name }}" class="w-full h-auto block rounded-lg">
                            @endif
                        </div>
                    @else
                        <p class="text-gray-500">Non configurée</p>
                    @endif
                </div>
            </div>

            <!-- Mission primaire (normal ou asymétrique) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden md:col-span-2">
                <div class="p-6">
                    @if($match->asymmetricPrimaryMission)
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Mission primaire asymétrique</h2>
                        <div class="space-y-4">
                            <!-- Onglets langue -->
                            <div class="flex gap-2 border-b border-gray-200">
                                <button onclick="switchTab('asymmetric-en')" class="px-4 py-2 font-semibold text-red-600 border-b-2 border-red-600 transition">
                                    Anglais
                                </button>
                                <button onclick="switchTab('asymmetric-fr')" class="px-4 py-2 font-semibold text-gray-600 border-b-2 border-transparent hover:text-gray-900 transition">
                                    Français
                                </button>
                            </div>

                            <!-- Contenu anglais -->
                            <div id="asymmetric-en" class="tab-content">
                                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $match->asymmetricPrimaryMission->name }}</h3>
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <p class="text-gray-700 whitespace-pre-wrap">{{ $match->asymmetricPrimaryMission->full_text ?? $match->asymmetricPrimaryMission->description }}</p>
                                </div>
                            </div>

                            <!-- Contenu français -->
                            <div id="asymmetric-fr" class="tab-content hidden">
                                @php
                                    $frenchNameTrans = \App\Models\Translation::where('resource_type', 'AsymmetricPrimaryMission')
                                        ->where('resource_id', $match->asymmetricPrimaryMission->id)
                                        ->where('locale', 'fr')
                                        ->where('field', 'name')
                                        ->first();
                                    $frenchTextTrans = \App\Models\Translation::where('resource_type', 'AsymmetricPrimaryMission')
                                        ->where('resource_id', $match->asymmetricPrimaryMission->id)
                                        ->where('locale', 'fr')
                                        ->where('field', 'full_text')
                                        ->first();
                                @endphp
                                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $frenchNameTrans?->translated_text ?? $match->asymmetricPrimaryMission->name }}</h3>
                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <p class="text-gray-700 whitespace-pre-wrap">{{ $frenchTextTrans?->translated_text ?? 'Traduction non disponible' }}</p>
                                </div>
                            </div>
                        </div>
                    @elseif($match->primaryMission)
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Mission primaire</h2>
                        <div class="space-y-4">
                            <!-- Onglets langue -->
                            <div class="flex gap-2 border-b border-gray-200">
                                <button onclick="switchTab('primary-en')" class="px-4 py-2 font-semibold text-red-600 border-b-2 border-red-600 transition">
                                    Anglais
                                </button>
                                <button onclick="switchTab('primary-fr')" class="px-4 py-2 font-semibold text-gray-600 border-b-2 border-transparent hover:text-gray-900 transition">
                                    Français
                                </button>
                            </div>

                            <!-- Contenu anglais -->
                            <div id="primary-en" class="tab-content">
                                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $match->primaryMission->name }}</h3>
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <p class="text-gray-700 whitespace-pre-wrap">{{ $match->primaryMission->full_text ?? $match->primaryMission->description }}</p>
                                </div>
                            </div>

                            <!-- Contenu français -->
                            <div id="primary-fr" class="tab-content hidden">
                                @php
                                    $frenchNameTrans = \App\Models\Translation::where('resource_type', 'PrimaryMission')
                                        ->where('resource_id', $match->primaryMission->id)
                                        ->where('locale', 'fr')
                                        ->where('field', 'name')
                                        ->first();
                                    $frenchTextTrans = \App\Models\Translation::where('resource_type', 'PrimaryMission')
                                        ->where('resource_id', $match->primaryMission->id)
                                        ->where('locale', 'fr')
                                        ->where('field', 'full_text')
                                        ->first();
                                @endphp
                                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $frenchNameTrans?->translated_text ?? $match->primaryMission->name }}</h3>
                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <p class="text-gray-700 whitespace-pre-wrap">{{ $frenchTextTrans?->translated_text ?? 'Traduction non disponible' }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Mission primaire</h2>
                        <p class="text-gray-500">Non configurée</p>
                    @endif
                </div>
            </div>

            <!-- Péripétie -->
        </div>

        <!-- Boutons d'action -->
        <div class="mt-8 flex gap-4 justify-center">
            <!-- Bouton "Modifier la configuration" seulement pour les matchs simples -->
            @if($matchType === 'player' && !$match->is_setup_validated)
                <a href="{{ route('player-matches.setup', $match->id) }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                    Modifier la configuration
                </a>
            @endif
            
            <!-- Bouton "Valider" pour les matchs simples non validés -->
            @if($matchType === 'player' && !$match->is_setup_validated)
                <form action="{{ route('player-matches.validate-setup', $match->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
                        Valider
                    </button>
                </form>
            @endif
            
            <!-- Message "Configuration validée" pour les matchs simples validés -->
            @if($matchType === 'player' && $match->is_setup_validated)
                <div class="px-6 py-3 bg-green-100 text-green-800 rounded-lg font-semibold border border-green-300">
                    Configuration validée
                </div>
            @endif
            
            <!-- Bouton "Retour" -->
            <a href="{{ $matchType === 'tournament' ? route('tournaments.matches.index', $match->tournament_id) : route('player-matches.show', $match->id) }}" class="px-6 py-3 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition">
                Retour
            </a>
        </div>
    </div>
</div>

<script>
function switchTab(tabId) {
    // Trouver le conteneur parent (section)
    const tabElement = document.getElementById(tabId);
    const section = tabElement.closest('.bg-white');
    
    // Masquer tous les onglets de cette section
    section.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Afficher l'onglet sélectionné
    tabElement.classList.remove('hidden');
    
    // Mettre à jour les boutons de cette section
    section.querySelectorAll('button[onclick^="switchTab"]').forEach(btn => {
        btn.classList.remove('text-red-600', 'border-b-2', 'border-red-600');
        btn.classList.add('text-gray-600', 'border-b-2', 'border-transparent');
    });
    
    event.target.classList.remove('text-gray-600', 'border-b-2', 'border-transparent');
    event.target.classList.add('text-red-600', 'border-b-2', 'border-red-600');
}
</script>
@endsection
