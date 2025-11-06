@extends('layouts.public')

@section('title', 'Modifier le match')

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
                        <h1 class="text-2xl font-bold text-white">Modifier le match</h1>
                        <p class="mt-1 text-red-100 text-xs">Modifiez les détails de votre proposition</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <form action="{{ route('player-matches.update', $playerMatch) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Type de match -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-3">Type de match *</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center p-4 border-2 cursor-pointer rounded-lg transition {{ $playerMatch->type === 'competitive' ? 'border-red-600 bg-red-50' : 'border-gray-200 hover:border-red-300' }}">
                        <input type="radio" name="type" value="competitive" {{ $playerMatch->type === 'competitive' ? 'checked' : '' }} class="w-4 h-4 text-red-600">
                        <span class="ml-3 font-medium text-gray-900">Compétitif</span>
                    </label>
                    <label class="flex items-center p-4 border-2 cursor-pointer rounded-lg transition {{ $playerMatch->type === 'narrative' ? 'border-red-600 bg-red-50' : 'border-gray-200 hover:border-red-300' }}">
                        <input type="radio" name="type" value="narrative" {{ $playerMatch->type === 'narrative' ? 'checked' : '' }} class="w-4 h-4 text-red-600">
                        <span class="ml-3 font-medium text-gray-900">Narratif</span>
                    </label>
                </div>
                @error('type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Points d'armée -->
            <div>
                <label for="army_points" class="block text-sm font-semibold text-gray-900 mb-2">Points d'armée *</label>
                <select id="army_points" name="army_points" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($armyPointsOptions as $value => $label)
                        <option value="{{ $value }}" {{ old('army_points', $playerMatch->army_points) == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Sélectionnez le nombre de points pour votre armée</p>
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
                            <option value="{{ $faction->name_fr ?? $faction->name }}" data-faction-id="{{ $faction->id }}" {{ old('faction', $playerMatch->faction) == ($faction->name_fr ?? $faction->name) ? 'selected' : '' }}>
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
                        @foreach($detachments as $detachment)
                            <option value="{{ $detachment->name }}" {{ old('detachment', $playerMatch->detachment) == $detachment->name ? 'selected' : '' }}>
                                {{ $detachment->name }}
                            </option>
                        @endforeach
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
                    <input type="text" id="city" name="city" value="{{ old('city', $playerMatch->city) }}" placeholder="ex: Paris" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    @error('city')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="department" class="block text-sm font-semibold text-gray-900 mb-2">Département</label>
                    <input type="text" id="department" name="department" value="{{ old('department', $playerMatch->department) }}" placeholder="ex: 75" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    @error('department')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Disponibilité -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-3">Type de disponibilité *</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center p-4 border-2 cursor-pointer rounded-lg transition {{ $playerMatch->availability_type === 'single' ? 'border-red-600 bg-red-50' : 'border-gray-200 hover:border-red-300' }}">
                        <input type="radio" name="availability_type" value="single" {{ $playerMatch->availability_type === 'single' ? 'checked' : '' }} class="w-4 h-4 text-red-600" onchange="updateAvailabilityFields()">
                        <span class="ml-3 font-medium text-gray-900">Date fixe</span>
                    </label>
                    <label class="flex items-center p-4 border-2 cursor-pointer rounded-lg transition {{ $playerMatch->availability_type === 'period' ? 'border-red-600 bg-red-50' : 'border-gray-200 hover:border-red-300' }}">
                        <input type="radio" name="availability_type" value="period" {{ $playerMatch->availability_type === 'period' ? 'checked' : '' }} class="w-4 h-4 text-red-600" onchange="updateAvailabilityFields()">
                        <span class="ml-3 font-medium text-gray-900">📆 Période</span>
                    </label>
                </div>
                @error('availability_type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date fixe -->
            <div id="single-availability" class="{{ $playerMatch->availability_type === 'single' ? '' : 'hidden' }}">
                <label for="available_at" class="block text-sm font-semibold text-gray-900 mb-2">Date et heure *</label>
                <input type="datetime-local" id="available_at" name="available_at" value="{{ old('available_at', $playerMatch->available_at?->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">Sélectionnez une date et heure futures</p>
                @error('available_at')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Période -->
            <div id="period-availability" class="{{ $playerMatch->availability_type === 'period' ? '' : 'hidden' }} space-y-4">
                <div>
                    <label for="available_from" class="block text-sm font-semibold text-gray-900 mb-2">Du *</label>
                    <input type="datetime-local" id="available_from" name="available_from" value="{{ old('available_from', $playerMatch->available_from?->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    @error('available_from')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="available_to" class="block text-sm font-semibold text-gray-900 mb-2">Au *</label>
                    <input type="datetime-local" id="available_to" name="available_to" value="{{ old('available_to', $playerMatch->available_to?->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    @error('available_to')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Commentaire -->
            <div>
                <label for="notes" class="block text-sm font-semibold text-gray-900 mb-2">Commentaire</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Ajoutez des détails sur votre match..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">{{ old('notes', $playerMatch->notes) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Optionnel - max 1000 caractères</p>
                @error('notes')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <button type="submit" class="flex-1 bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                    Enregistrer les modifications
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
        </div>
    </div>
</div>
@endsection
