@extends('layouts.public')

@section('title', 'Demandes de participation')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- En-tête gradient rouge -->
    <div class="bg-gradient-to-r from-red-600 to-red-700 border-b border-red-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">📋 Demandes de participation</h1>
                    <p class="text-red-100">Gérez les demandes pour votre match</p>
                </div>
                <a href="{{ route('player-matches.show', $playerMatch) }}" class="px-4 py-2 bg-white text-red-600 rounded-lg font-semibold hover:bg-red-50 transition">
                    ← Retour au match
                </a>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($pendingRequests->count() > 0)
            <div class="space-y-6">
                @foreach($pendingRequests as $request)
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition">
                        <div class="p-6">
                            <!-- En-tête avec nom et infos principales -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h2 class="text-xl font-bold text-gray-900">{{ $request->requester->name }}</h2>
                                    <p class="text-sm text-gray-600 mt-1">Demande reçue le {{ $request->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    ⋳ En attente
                                </span>
                            </div>

                            <!-- Détails du joueur -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                                <!-- Faction -->
                                <div>
                                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Faction</p>
                                    <p class="text-lg font-semibold text-gray-900 mt-1">{{ $request->faction }}</p>
                                </div>

                                <!-- Détachement -->
                                <div>
                                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Détachement</p>
                                    <p class="text-lg font-semibold text-gray-900 mt-1">{{ $request->detachment ?? '-' }}</p>
                                </div>

                                <!-- Ratio de victoire -->
                                <div>
                                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Ratio de victoire</p>
                                    <div class="mt-1 flex items-baseline gap-2">
                                        <p class="text-lg font-semibold text-gray-900">{{ $request->requester->win_ratio }}%</p>
                                        <p class="text-xs text-gray-600">({{ $request->requester->total_matches }} matchs)</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Message du demandeur -->
                            @if($request->message)
                                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">Message du demandeur</p>
                                    <p class="text-gray-700">{{ $request->message }}</p>
                                </div>
                            @endif

                            <!-- Boutons d'action -->
                            <div class="flex gap-3 pt-4 border-t border-gray-200">
                                <form action="{{ route('player-match-requests.accept', $request) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
                                        Accepter
                                    </button>
                                </form>
                                <button type="button" onclick="toggleRejectForm({{ $request->id }})" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition">
                                    ✗ Refuser
                                </button>
                            </div>

                            <!-- Formulaire de refus (caché par défaut) -->
                            <div id="reject-form-{{ $request->id }}" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <form action="{{ route('player-match-requests.reject', $request) }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="creator_response_{{ $request->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                                            Message de refus (optionnel)
                                        </label>
                                        <textarea 
                                            id="creator_response_{{ $request->id }}" 
                                            name="creator_response" 
                                            rows="3" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                            placeholder="Expliquez pourquoi vous refusez cette demande..."
                                        ></textarea>
                                    </div>
                                    <div class="flex gap-3">
                                        <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition">
                                            Confirmer le refus
                                        </button>
                                        <button type="button" onclick="toggleRejectForm({{ $request->id }})" class="flex-1 px-4 py-2 bg-gray-400 text-white rounded-lg font-semibold hover:bg-gray-500 transition">
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <p class="text-gray-600">Aucune demande en attente</p>
            </div>
        @endif
    </div>
</div>

<script>
function toggleRejectForm(requestId) {
    const form = document.getElementById(`reject-form-${requestId}`);
    form.classList.toggle('hidden');
}
</script>
@endsection
