@extends('layouts.public')

@section('title', 'Proposer un match')

@section('content')
<!-- Page Header -->
<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('player-matches.index') }}" class="text-red-600 hover:text-red-700 text-sm font-medium mb-2 inline-block">
            ← Retour aux matchs
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Proposer un match</h1>
        <p class="mt-2 text-gray-600">Créez une proposition de match pour les autres joueurs</p>
    </div>
</div>

<!-- Hero Section with Red Background -->
<div style="background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); padding: 3rem 0; margin-bottom: 2rem;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <!-- Left side - Text -->
            <div>
                <h2 style="color: #ffffff; font-size: 2rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.2;">
                    Trouvez votre prochain adversaire
                </h2>
                <p style="color: #fecaca; font-size: 1.125rem; margin-bottom: 1.5rem; line-height: 1.6;">
                    Proposez un match avec vos préférences et attendez que d'autres joueurs vous rejoignent. Définissez votre disponibilité, votre faction et vos conditions.
                </p>
                <ul style="color: #fecaca; font-size: 1rem; space-y: 0.75rem;">
                    <li style="margin-bottom: 0.75rem;">✓ Choisissez le type de match (compétitif ou narratif)</li>
                    <li style="margin-bottom: 0.75rem;">✓ Définissez votre disponibilité</li>
                    <li style="margin-bottom: 0.75rem;">✓ Spécifiez votre localisation</li>
                    <li>✓ Attendez les candidatures</li>
                </ul>
            </div>
            
            <!-- Right side - Icon/Visual -->
            <div style="text-align: center;">
                <svg style="width: 200px; height: 200px; margin: 0 auto; opacity: 0.9;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke="white">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Form -->
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <form action="{{ route('player-matches.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Type de match -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-3">Type de match *</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center p-4 border-2 cursor-pointer rounded-lg transition {{ old('type') === 'competitive' ? 'border-red-600 bg-red-50' : 'border-gray-200 hover:border-red-300' }}">
                        <input type="radio" name="type" value="competitive" {{ old('type') === 'competitive' ? 'checked' : '' }} class="w-4 h-4 text-red-600">
                        <span class="ml-3 font-medium text-gray-900">⚔️ Compétitif</span>
                    </label>
                    <label class="flex items-center p-4 border-2 cursor-pointer rounded-lg transition {{ old('type') === 'narrative' ? 'border-red-600 bg-red-50' : 'border-gray-200 hover:border-red-300' }}">
                        <input type="radio" name="type" value="narrative" {{ old('type') === 'narrative' ? 'checked' : '' }} class="w-4 h-4 text-red-600">
                        <span class="ml-3 font-medium text-gray-900">📖 Narratif</span>
                    </label>
                </div>
                @error('type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Points d'armée -->
            <div>
                <label for="army_points" class="block text-sm font-semibold text-gray-900 mb-2">Points d'armée *</label>
                <input type="number" id="army_points" name="army_points" value="{{ old('army_points', 2000) }}" min="500" max="5000" step="100" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                <p class="mt-1 text-xs text-gray-500">Entre 500 et 5000 points</p>
                @error('army_points')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Faction et Détachement -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="faction" class="block text-sm font-semibold text-gray-900 mb-2">Faction *</label>
                    <select id="faction" name="faction" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required onchange="updateDetachments()">
                        <option value="">-- Sélectionnez une faction --</option>
                        @foreach($factions as $faction)
                            <option value="{{ $faction->name_fr ?? $faction->name }}" data-faction-id="{{ $faction->id }}" {{ old('faction') == ($faction->name_fr ?? $faction->name) ? 'selected' : '' }}>
                                {{ $faction->name_fr ?? $faction->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Seules les factions avec détachements disponibles sont affichées</p>
                    @error('faction')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="detachment" class="block text-sm font-semibold text-gray-900 mb-2">Détachement *</label>
                    <select id="detachment" name="detachment" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                        <option value="">-- Sélectionnez d'abord une faction --</option>
                    </select>
                    @error('detachment')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Localisation -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="city" class="block text-sm font-semibold text-gray-900 mb-2">Ville *</label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="ex: Paris" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    @error('city')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="department" class="block text-sm font-semibold text-gray-900 mb-2">Département *</label>
                    <input type="text" id="department" name="department" value="{{ old('department') }}" placeholder="ex: 75" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    @error('department')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Disponibilité -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-3">Type de disponibilité *</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center p-4 border-2 cursor-pointer rounded-lg transition {{ old('availability_type') === 'single' ? 'border-red-600 bg-red-50' : 'border-gray-200 hover:border-red-300' }}">
                        <input type="radio" name="availability_type" value="single" {{ old('availability_type') === 'single' ? 'checked' : '' }} class="w-4 h-4 text-red-600" onchange="updateAvailabilityFields()">
                        <span class="ml-3 font-medium text-gray-900">📅 Date fixe</span>
                    </label>
                    <label class="flex items-center p-4 border-2 cursor-pointer rounded-lg transition {{ old('availability_type') === 'period' ? 'border-red-600 bg-red-50' : 'border-gray-200 hover:border-red-300' }}">
                        <input type="radio" name="availability_type" value="period" {{ old('availability_type') === 'period' ? 'checked' : '' }} class="w-4 h-4 text-red-600" onchange="updateAvailabilityFields()">
                        <span class="ml-3 font-medium text-gray-900">📆 Période</span>
                    </label>
                </div>
                @error('availability_type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date fixe -->
            <div id="single-availability" class="hidden">
                <label for="available_at" class="block text-sm font-semibold text-gray-900 mb-2">Date et heure *</label>
                <input type="datetime-local" id="available_at" name="available_at" value="{{ old('available_at') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">Sélectionnez une date et heure futures</p>
                @error('available_at')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Période -->
            <div id="period-availability" class="hidden space-y-4">
                <div>
                    <label for="available_from" class="block text-sm font-semibold text-gray-900 mb-2">Du *</label>
                    <input type="datetime-local" id="available_from" name="available_from" value="{{ old('available_from') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    @error('available_from')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="available_to" class="block text-sm font-semibold text-gray-900 mb-2">Au *</label>
                    <input type="datetime-local" id="available_to" name="available_to" value="{{ old('available_to') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    @error('available_to')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Commentaire -->
            <div>
                <label for="notes" class="block text-sm font-semibold text-gray-900 mb-2">Commentaire</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Ajoutez des détails sur votre match..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">{{ old('notes') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Optionnel - max 1000 caractères</p>
                @error('notes')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <button type="submit" class="flex-1 bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                    🚀 Proposer le match
                </button>
                <a href="{{ route('player-matches.index') }}" class="flex-1 bg-gray-200 text-gray-900 py-3 rounded-lg font-semibold hover:bg-gray-300 transition text-center">
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

function updateAvailabilityFields() {
    const type = document.querySelector('input[name="availability_type"]:checked').value;
    const singleDiv = document.getElementById('single-availability');
    const periodDiv = document.getElementById('period-availability');

    if (type === 'single') {
        singleDiv.classList.remove('hidden');
        periodDiv.classList.add('hidden');
    } else {
        singleDiv.classList.add('hidden');
        periodDiv.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateAvailabilityFields();
    // Si une faction est déjà sélectionnée (après erreur de validation)
    if (document.getElementById('faction').value) {
        updateDetachments();
    }
});
</script>
@endsection
