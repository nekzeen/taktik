@extends('layouts.public')

@section('title', 'Répondre au match')

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md sm:rounded-lg mb-6 p-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <a href="{{ route('player-matches.show', $playerMatch) }}" class="text-white hover:text-red-100 text-xs font-medium mb-1 inline-block">
                            ← Retour au match
                        </a>
                        <h1 class="text-2xl font-bold text-white">Répondre au match</h1>
                        <p class="mt-1 text-red-100 text-xs">Proposez votre participation avec votre faction et détachement</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <!-- Détails du match -->
        <div class="mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails du match</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-600">Créateur</p>
                    <p class="font-semibold text-gray-900">{{ $playerMatch->creator->name }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Type</p>
                    <p class="font-semibold text-gray-900">{{ $playerMatch->getTypeLabel() }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Points d'armée</p>
                    <p class="font-semibold text-gray-900">{{ $playerMatch->army_points }} pts</p>
                </div>
                <div>
                    <p class="text-gray-600">Localisation</p>
                    <p class="font-semibold text-gray-900">{{ $playerMatch->getLocationDisplay() }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Faction du créateur</p>
                    <p class="font-semibold text-gray-900">{{ $playerMatch->faction }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Disponibilité</p>
                    <p class="font-semibold text-gray-900">{{ $playerMatch->getAvailabilityDisplay() }}</p>
                </div>
            </div>
        </div>

        <!-- Formulaire de réponse -->
        <form action="{{ route('player-match-requests.store', $playerMatch) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Faction et Détachement -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="faction" class="block text-sm font-semibold text-gray-900 mb-2">Votre Faction *</label>
                    <select id="faction" name="faction" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required onchange="updateDetachments()">
                        <option value="">-- Sélectionnez une faction --</option>
                        @foreach($factions as $faction)
                            @php
                                $displayName = !empty(trim($faction->name_fr)) ? $faction->name_fr : $faction->name;
                            @endphp
                            <option value="{{ $displayName }}" data-faction-id="{{ $faction->id }}" {{ old('faction') == $displayName ? 'selected' : '' }}>
                                {{ $displayName }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Seules les factions avec détachements disponibles sont affichées</p>
                    @error('faction')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="detachment" class="block text-sm font-semibold text-gray-900 mb-2">Votre Détachement *</label>
                    <select id="detachment" name="detachment" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                        <option value="">-- Sélectionnez d'abord une faction --</option>
                    </select>
                    @error('detachment')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Message -->
            <div>
                <label for="message" class="block text-sm font-semibold text-gray-900 mb-2">Message au créateur</label>
                <textarea id="message" name="message" rows="4" placeholder="Laissez un message au créateur du match..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">{{ old('message') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Optionnel - max 1000 caractères</p>
                @error('message')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <button type="submit" class="flex-1 bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                    Envoyer ma demande
                </button>
                <a href="{{ route('player-matches.show', $playerMatch) }}" class="flex-1 bg-gray-200 text-gray-900 py-3 rounded-lg font-semibold hover:bg-gray-300 transition text-center">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const factionDetachments = {!! json_encode($factions->mapWithKeys(function($faction) {
    return [$faction->id => $faction->detachments->map(function($d) { return ['id' => $d->id, 'name' => $d->name]; })->values()];
})->toArray()) !!};

function updateDetachments() {
    const factionSelect = document.getElementById('faction');
    const selectedOption = factionSelect.options[factionSelect.selectedIndex];
    const factionId = selectedOption.getAttribute('data-faction-id');
    const detachmentSelect = document.getElementById('detachment');
    
    detachmentSelect.innerHTML = '<option value="">-- Sélectionnez un détachement --</option>';
    
    if (factionId && factionDetachments[factionId] && factionDetachments[factionId].length > 0) {
        factionDetachments[factionId].forEach((detachment) => {
            const option = document.createElement('option');
            option.value = detachment.name;
            option.textContent = detachment.name;
            detachmentSelect.appendChild(option);
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('faction').value) {
        updateDetachments();
    }
});
</script>
        </div>
    </div>
</div>
@endsection
