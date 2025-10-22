@extends('layouts.public')

@section('title', 'Détail du match')

@section('content')
<!-- Page Header -->
<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('player-matches.index') }}" class="text-red-600 hover:text-red-700 text-sm font-medium mb-2 inline-block">
            ← Retour aux matchs
        </a>
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $playerMatch->getTypeLabel() }}</h1>
                <p class="mt-2 text-gray-600">📍 {{ $playerMatch->getLocationDisplay() }}</p>
            </div>
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium {{ $playerMatch->status === 'open' ? 'bg-blue-100 text-blue-800' : ($playerMatch->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($playerMatch->status === 'completed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                {{ $playerMatch->getStatusLabel() }}
            </span>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            ✗ {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Créateur du match -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Créateur du match</h2>
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
                @if($playerMatch->faction)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600"><strong>Faction:</strong> {{ $playerMatch->faction }}</p>
                        @if($playerMatch->detachment)
                            <p class="text-sm text-gray-600 mt-1"><strong>Détachement:</strong> {{ $playerMatch->detachment }}</p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Adversaire -->
            @if($playerMatch->opponent)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Adversaire</h2>
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
                            <p class="mt-4 text-lg font-semibold text-yellow-600">⚖️ Match nul</p>
                        @else
                            <p class="mt-4 text-lg font-semibold text-red-600">
                                🏆 {{ $playerMatch->winner->name }} a gagné
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
                                                    ✓ Accepter
                                                </button>
                                            </form>
                                            <button type="button" class="flex-1 bg-red-600 text-white py-2 rounded font-semibold hover:bg-red-700 transition text-sm" onclick="toggleRejectForm({{ $request->id }})">
                                                ✗ Refuser
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
                            ⚠️ Cette proposition a expiré. Elle sera supprimée si aucun joueur ne s'inscrit.
                        </div>
                    @else
                        @if(auth()->id() !== $playerMatch->creator_id)
                            @if($userRequest)
                                @if($userRequest->status === 'pending')
                                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                                        ⏳ Demande en cours d'examen
                                    </div>
                                @elseif($userRequest->status === 'accepted')
                                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                                        ✓ Demande acceptée ! Le match est confirmé.
                                    </div>
                                @elseif($userRequest->status === 'rejected')
                                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                                        ✗ Demande refusée
                                        @if($userRequest->creator_response)
                                            <p class="mt-2 text-xs">{{ $userRequest->creator_response }}</p>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <a href="{{ route('player-match-requests.create', $playerMatch) }}" class="block w-full bg-red-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-red-700 transition text-center">
                                    ✓ Répondre à ce match
                                </a>
                            @endif
                        @else
                            <p class="text-gray-600 text-sm">En attente de réponses...</p>
                        @endif
                    @endif
                @elseif($playerMatch->status === 'confirmed')
                    <p class="text-gray-700 mb-4">Le match est confirmé entre {{ $playerMatch->creator->name }} et {{ $playerMatch->opponent->name }}.</p>
                    @if(auth()->id() === $playerMatch->creator_id || auth()->id() === $playerMatch->opponent_id)
                        <button type="button" class="w-full bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition" onclick="document.getElementById('score-form').classList.toggle('hidden')">
                            Enregistrer le score
                        </button>
                    @endif
                @elseif($playerMatch->status === 'completed')
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                        ✓ Match terminé
                    </div>
                @endif
            </div>

            <!-- Actions du créateur -->
            @if(auth()->id() === $playerMatch->creator_id)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Gestion</h3>
                    <div class="space-y-2">
                        @if($playerMatch->status === 'open')
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

    <!-- Formulaire de score -->
    @if($playerMatch->status === 'confirmed' && (auth()->id() === $playerMatch->creator_id || auth()->id() === $playerMatch->opponent_id))
        <div id="score-form" class="hidden mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Enregistrer le score</h2>
            <form action="{{ route('player-matches.set-score', $playerMatch) }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">{{ $playerMatch->creator->name }}</label>
                        <input type="number" name="creator_score" min="0" value="{{ old('creator_score') }}" class="w-full px-4 py-3 text-center text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">{{ $playerMatch->opponent->name }}</label>
                        <input type="number" name="opponent_score" min="0" value="{{ old('opponent_score') }}" class="w-full px-4 py-3 text-center text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    </div>
                </div>
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                        Enregistrer le score
                    </button>
                    <button type="button" class="flex-1 bg-gray-200 text-gray-900 py-3 rounded-lg font-semibold hover:bg-gray-300 transition" onclick="document.getElementById('score-form').classList.add('hidden')">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>

<script>
function toggleRejectForm(requestId) {
    const form = document.getElementById('reject-form-' + requestId);
    form.classList.toggle('hidden');
}
</script>
@endsection
