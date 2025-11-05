@extends('layouts.public')

@section('title', 'Détail du match')

@php
use Illuminate\Support\Facades\DB;
@endphp

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md sm:rounded-lg mb-6 p-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <a href="{{ route('player-matches.index') }}" class="text-white hover:text-red-100 text-xs font-medium mb-1 inline-block">
                            ← Retour aux matchs
                        </a>
                        <h1 class="text-2xl font-bold text-white">{{ $playerMatch->getTypeLabel() }}</h1>
                        <p class="mt-1 text-red-100 text-xs">📍 {{ $playerMatch->getLocationDisplay() }}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $playerMatch->status === 'open' ? 'bg-blue-100 text-blue-800' : ($playerMatch->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($playerMatch->status === 'completed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }} whitespace-nowrap">
                        {{ $playerMatch->getStatusLabel() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Créateur du match -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Créateur du match</h2>
                <div class="space-y-4">
                    <!-- Nom et score -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-lg font-semibold text-gray-900">{{ $playerMatch->creator->name }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $playerMatch->army_points }} pts</p>
                        </div>
                        @if($playerMatch->status === 'completed')
                            <div class="text-right">
                                <p class="text-3xl font-bold text-gray-900">{{ $playerMatch->creator_score }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Faction et Détachement -->
                    @if($playerMatch->faction)
                        <div class="pt-4 border-t border-gray-200 space-y-2">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Faction</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">{{ $playerMatch->faction }}</p>
                            </div>
                            @if($playerMatch->detachment)
                                <div>
                                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Détachement</p>
                                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $playerMatch->detachment }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Ratio de victoire -->
                    @if($creatorStats)
                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Ratio de victoire</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <p class="text-2xl font-bold text-gray-900">{{ $creatorStats['win_ratio'] }}%</p>
                                <p class="text-xs text-gray-600">({{ $creatorStats['total_matches'] }} matchs)</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Adversaire -->
            @if($playerMatch->opponent)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Adversaire</h2>
                    <div class="space-y-4">
                        <!-- Nom et score -->
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lg font-semibold text-gray-900">{{ $playerMatch->opponent->name }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $playerMatch->army_points }} pts</p>
                            </div>
                            @if($playerMatch->status === 'completed')
                                <div class="text-right">
                                    <p class="text-3xl font-bold text-gray-900">{{ $playerMatch->opponent_score }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Faction et Détachement -->
                        @if($playerMatch->opponent_faction)
                            <div class="pt-4 border-t border-gray-200 space-y-2">
                                <div>
                                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Faction</p>
                                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $playerMatch->opponent_faction }}</p>
                                </div>
                                @if($playerMatch->opponent_detachment)
                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Détachement</p>
                                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $playerMatch->opponent_detachment }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Ratio de victoire -->
                        @if($opponentStats)
                            <div class="pt-4 border-t border-gray-200">
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Ratio de victoire</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <p class="text-2xl font-bold text-gray-900">{{ $opponentStats['win_ratio'] }}%</p>
                                    <p class="text-xs text-gray-600">({{ $opponentStats['total_matches'] }} matchs)</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Détails du match -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Type:</span>
                        <span class="font-semibold text-gray-900">{{ $playerMatch->getTypeLabel() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Points d'armée:</span>
                        <span class="font-semibold text-gray-900">{{ $playerMatch->army_points }} pts</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Localisation:</span>
                        <span class="font-semibold text-gray-900">{{ $playerMatch->getLocationDisplay() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Disponibilité:</span>
                        <span class="font-semibold text-gray-900">{{ $playerMatch->getAvailabilityDisplay() }}</span>
                    </div>
                    @if($playerMatch->status === 'completed' && $playerMatch->played_at)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Joué le:</span>
                            <span class="font-semibold text-gray-900">{{ $playerMatch->played_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Résumé de la configuration -->
            @if($playerMatch->is_setup_complete)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Configuration du match</h2>
                    <div class="space-y-3">
                        @if($playerMatch->deployment_mode)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Zone de déploiement:</span>
                                <span class="font-semibold text-gray-900">
                                    @php
                                        $deploymentFr = null;
                                        
                                        // Chercher dans les trois tables de cartes de déploiement
                                        $tables = [
                                            'asymmetric_warfare_deployment_cards' => 'AsymmetricWarfareDeploymentCard',
                                            'strike_force_deployment_cards' => 'StrikeForceDeploymentCard',
                                            'incursion_deployment_cards' => 'IncursionDeploymentCard',
                                        ];
                                        
                                        foreach ($tables as $tableName => $resourceType) {
                                            $deploymentCard = DB::table($tableName)
                                                ->where('name', $playerMatch->deployment_mode)
                                                ->first();
                                            
                                            if ($deploymentCard) {
                                                $deploymentFr = DB::table('translations')
                                                    ->where('resource_type', $resourceType)
                                                    ->where('resource_id', $deploymentCard->id)
                                                    ->where('field', 'name')
                                                    ->where('locale', 'fr')
                                                    ->value('translated_text');
                                                
                                                if ($deploymentFr) {
                                                    break; // Sortir de la boucle si traduction trouvée
                                                }
                                            }
                                        }
                                    @endphp
                                    @if($deploymentFr){{ $deploymentFr }} ({{ $playerMatch->deployment_mode }})@else{{ $playerMatch->deployment_mode }}@endif
                                </span>
                            </div>
                        @endif
                        @if($playerMatch->terrainLayout)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Disposition de terrain:</span>
                                <span class="font-semibold text-gray-900">
                                    @php
                                        $terrainFr = DB::table('translations')
                                            ->where('resource_type', 'TerrainLayout')
                                            ->where('resource_id', $playerMatch->terrainLayout->id)
                                            ->where('field', 'name')
                                            ->where('locale', 'fr')
                                            ->value('translated_text');
                                    @endphp
                                    @if($terrainFr){{ $terrainFr }} ({{ $playerMatch->terrainLayout->name }})@else{{ $playerMatch->terrainLayout->name }}@endif
                                </span>
                            </div>
                        @endif
                        @if($playerMatch->primaryMission)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Mission primaire:</span>
                                <span class="font-semibold text-gray-900">
                                    @php
                                        $primaryFr = DB::table('translations')
                                            ->where('resource_type', 'PrimaryMission')
                                            ->where('resource_id', $playerMatch->primaryMission->id)
                                            ->where('field', 'name')
                                            ->where('locale', 'fr')
                                            ->value('translated_text');
                                    @endphp
                                    @if($primaryFr){{ $primaryFr }} ({{ $playerMatch->primaryMission->name }})@else{{ $playerMatch->primaryMission->name }}@endif
                                </span>
                            </div>
                        @endif
                        @if($playerMatch->secondaryMission)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Mission secondaire:</span>
                                <span class="font-semibold text-gray-900">
                                    @php
                                        $secondaryFr = DB::table('translations')
                                            ->where('resource_type', 'SecondaryMission')
                                            ->where('resource_id', $playerMatch->secondaryMission->id)
                                            ->where('field', 'name')
                                            ->where('locale', 'fr')
                                            ->value('translated_text');
                                    @endphp
                                    @if($secondaryFr){{ $secondaryFr }} ({{ $playerMatch->secondaryMission->name }})@else{{ $playerMatch->secondaryMission->name }}@endif
                                </span>
                            </div>
                        @endif
                        @if($playerMatch->twist_mission_id)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Péripétie:</span>
                                <span class="font-semibold text-gray-900">
                                    @php
                                        $twistFr = null;
                                        if ($playerMatch->twistMission) {
                                            $twistFr = DB::table('translations')
                                                ->where('resource_type', 'TwistMission')
                                                ->where('resource_id', $playerMatch->twistMission->id)
                                                ->where('field', 'name')
                                                ->where('locale', 'fr')
                                                ->value('translated_text');
                                        }
                                    @endphp
                                    @if($twistFr){{ $twistFr }} ({{ $playerMatch->twistMission->name }})@else{{ $playerMatch->twistMission->name ?? 'Non configurée' }}@endif
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Résultat -->
            @if($playerMatch->status === 'completed')
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Résultat</h2>
                    <div class="text-center">
                        <div class="flex items-center justify-center gap-8">
                            <div>
                                <p class="text-sm text-gray-600 mb-2">{{ $playerMatch->creator->name }}</p>
                                <p class="text-4xl font-bold text-gray-900">{{ $playerMatch->creator_score }}</p>
                            </div>
                            <div class="text-2xl font-bold text-gray-400">-</div>
                            <div>
                                <p class="text-sm text-gray-600 mb-2">{{ $playerMatch->opponent->name }}</p>
                                <p class="text-4xl font-bold text-gray-900">{{ $playerMatch->opponent_score }}</p>
                            </div>
                        </div>
                        @if($playerMatch->is_draw)
                            <p class="mt-4 text-lg font-semibold text-yellow-600">Match nul</p>
                        @else
                            <p class="mt-4 text-lg font-semibold text-red-600">
                                {{ $playerMatch->winner->name }} a gagné
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Commentaire -->
            @if($playerMatch->notes)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Commentaire</h2>
                    <p class="text-gray-700">{{ $playerMatch->notes }}</p>
                </div>
            @endif

            <!-- Demandes de participation (pour le créateur) -->
            @if(auth()->id() === $playerMatch->creator_id && $playerMatch->status === 'open')
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Demandes de participation</h2>
                    
                    @if($requests->count() > 0)
                        <div class="space-y-4">
                            @foreach($requests as $request)
                                <div class="border border-gray-200 rounded-lg p-4 {{ $request->status === 'pending' ? 'bg-blue-50' : ($request->status === 'accepted' ? 'bg-green-50' : 'bg-red-50') }}">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $request->requester->name }}</p>
                                            <p class="text-sm text-gray-600 mt-1">{{ $request->faction }} - {{ $request->detachment }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $request->status === 'pending' ? 'bg-blue-100 text-blue-800' : ($request->status === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $request->status === 'pending' ? 'En attente' : ($request->status === 'accepted' ? 'Acceptée' : 'Refusée') }}
                                        </span>
                                    </div>

                                    @if($request->message)
                                        <p class="text-sm text-gray-700 mb-3 p-3 bg-white rounded border border-gray-200">{{ $request->message }}</p>
                                    @endif

                                    @if($request->status === 'pending')
                                        <div class="flex gap-2">
                                            <form action="{{ route('player-match-requests.accept', $request) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded font-semibold hover:bg-green-700 transition text-sm">
                                                    Accepter
                                                </button>
                                            </form>
                                            <button type="button" class="flex-1 bg-red-600 text-white py-2 rounded font-semibold hover:bg-red-700 transition text-sm" onclick="toggleRejectForm({{ $request->id }})">
                                                Refuser
                                            </button>
                                        </div>

                                        <!-- Formulaire de refus caché -->
                                        <div id="reject-form-{{ $request->id }}" class="hidden mt-3 p-3 bg-white rounded border border-gray-200">
                                            <form action="{{ route('player-match-requests.reject', $request) }}" method="POST">
                                                @csrf
                                                <textarea name="creator_response" placeholder="Message de refus (optionnel)" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded text-sm mb-2"></textarea>
                                                <div class="flex gap-2">
                                                    <button type="submit" class="flex-1 bg-red-600 text-white py-2 rounded font-semibold hover:bg-red-700 transition text-sm">
                                                        Confirmer le refus
                                                    </button>
                                                    <button type="button" class="flex-1 bg-gray-300 text-gray-900 py-2 rounded font-semibold hover:bg-gray-400 transition text-sm" onclick="toggleRejectForm({{ $request->id }})">
                                                        Annuler
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @elseif($request->status === 'rejected' && $request->creator_response)
                                        <p class="text-sm text-gray-700 mt-2 p-2 bg-white rounded border border-gray-200">
                                            <strong>Votre réponse:</strong> {{ $request->creator_response }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600">Aucune demande pour le moment</p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Statut du match -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                @if($playerMatch->status === 'open')
                    @if(!$playerMatch->isAvailable())
                        <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                            Cette proposition a expiré. Elle sera supprimée si aucun joueur ne s'inscrit.
                        </div>
                    @else
                        @if(auth()->id() !== $playerMatch->creator_id)
                            @if($userRequest)
                                @if($userRequest->status === 'pending')
                                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                                        Demande en cours d'examen
                                    </div>
                                @elseif($userRequest->status === 'accepted')
                                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                                        Demande acceptée ! Le match est confirmé.
                                    </div>
                                @elseif($userRequest->status === 'rejected')
                                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                                        Demande refusée
                                        @if($userRequest->creator_response)
                                            <p class="mt-2 text-xs">{{ $userRequest->creator_response }}</p>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <a href="{{ route('player-match-requests.create', $playerMatch) }}" class="block w-full bg-red-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-red-700 transition text-center">
                                    Répondre à ce match
                                </a>
                            @endif
                        @else
                            <div class="space-y-2">
                                @if($playerMatch->is_setup_validated)
                                    <a href="{{ route('player-matches.summary', $playerMatch) }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                                        Voir la configuration
                                    </a>
                                @else
                                    <a href="{{ route('player-matches.setup', $playerMatch) }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                                        Configurer
                                    </a>
                                @endif
                                <p class="text-gray-600 text-sm text-center">En attente de réponses...</p>
                            </div>
                        @endif
                    @endif
                @elseif($playerMatch->status === 'confirmed')
                    <p class="text-gray-700 mb-4">Le match est confirmé entre {{ $playerMatch->creator->name }} et {{ $playerMatch->opponent->name }}.</p>
                    @if(auth()->id() === $playerMatch->creator_id)
                        <!-- Créateur du match : peut configurer et enregistrer le score -->
                        <div class="space-y-2">
                            @if($playerMatch->is_setup_validated)
                                <a href="{{ route('player-matches.summary', $playerMatch) }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                                    Voir la configuration
                                </a>
                            @else
                                <a href="{{ route('player-matches.setup', $playerMatch) }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                                    Configurer
                                </a>
                            @endif
                            <a href="{{ route('player-matches.score', $playerMatch) }}" class="block w-full text-center bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                                Voir le scoring
                            </a>
                        </div>
                    @elseif(auth()->id() === $playerMatch->opponent_id)
                        <!-- Adversaire : peut seulement voir la configuration (pas de modification) -->
                        <div class="space-y-2">
                            <a href="{{ route('player-matches.summary', $playerMatch) }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                                Voir la configuration
                            </a>
                            <a href="{{ route('player-matches.show', $playerMatch) }}" class="block w-full text-center bg-gray-600 text-white py-2 rounded-lg font-semibold hover:bg-gray-700 transition">
                                Résumé
                            </a>
                            <a href="{{ route('player-matches.view-score', $playerMatch) }}" class="block w-full text-center bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                                Voir le scoring
                            </a>
                        </div>
                    @endif
                @elseif($playerMatch->status === 'completed')
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                        Match terminé
                    </div>
                @endif
            </div>

            <!-- Actions du créateur -->
            @if(auth()->id() === $playerMatch->creator_id)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Gestion</h3>
                    <div class="space-y-2">
                        @if($playerMatch->status === 'open' && !$playerMatch->is_setup_validated)
                            <a href="{{ route('player-matches.edit', $playerMatch) }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                                Modifier
                            </a>
                        @endif
                        @if($playerMatch->status !== 'completed')
                            <form action="{{ route('player-matches.cancel', $playerMatch) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition" onclick="return confirm('Êtes-vous sûr ?')">
                                    Annuler le match
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

<script>
function toggleRejectForm(requestId) {
    const form = document.getElementById('reject-form-' + requestId);
    form.classList.toggle('hidden');
}
</script>
@endsection
