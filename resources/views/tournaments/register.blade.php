@extends('layouts.public')

@section('title', 'Inscription - ' . $tournament->name)

@section('content')
<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('tournaments.show', $tournament) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium mb-2 inline-block">
                    ← Retour au tournoi
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Inscription au tournoi</h1>
                <p class="text-gray-600 mt-1">{{ $tournament->name }}</p>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('tournaments.register', $tournament) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Informations de votre liste</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="faction_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Faction *
                            </label>
                            <select name="faction_id" 
                                    id="faction_id" 
                                    required
                                    class="block w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary-600 bg-white">
                                <option value="">-- Sélectionner une faction --</option>
                                @foreach($factions as $faction)
                                    <option value="{{ $faction->id }}" @selected(old('faction_id') == $faction->id)>{{ $faction->name }}</option>
                                @endforeach
                            </select>
                            @error('faction_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="detachment" class="block text-sm font-medium text-gray-700 mb-2">
                                Détachement *
                            </label>
                            <select name="detachment" 
                                    id="detachment" 
                                    required
                                    class="block w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary-600 bg-white">
                                <option value="">-- Sélectionner un détachement --</option>
                            </select>
                            @error('detachment')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Votre liste d'armée</h2>
                    <p class="text-sm text-gray-600 mb-6">
                        Veuillez uploader votre liste d'armée au format PDF. Taille maximale : 10 MB.
                    </p>

                    <div>
                        <label for="pdf" class="block text-sm font-medium text-gray-700 mb-2">
                            Fichier PDF *
                        </label>
                        <input type="file" 
                               name="pdf" 
                               id="pdf" 
                               accept=".pdf"
                               required
                               class="block w-full px-4 py-2 border-2 border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-primary-600">
                        @error('pdf')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="file-preview" class="hidden mt-4">
                        <div class="border-2 border-dashed border-primary-300 rounded-lg p-4 bg-primary-50">
                            <div class="flex items-center space-x-3">
                                <svg class="w-8 h-8 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <p id="file-name" class="text-sm font-medium text-gray-900"></p>
                                    <p id="file-size" class="text-xs text-gray-500"></p>
                                </div>
                                <button type="button" onclick="clearFile()" class="text-primary-600 hover:text-primary-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6">
                    <h3 class="font-semibold text-blue-900 mb-3">ℹ️ Informations importantes</h3>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li>Votre liste sera validée par un organisateur</li>
                        <li>Vous pourrez la modifier tant qu'elle n'est pas validée</li>
                        <li>Assurez-vous que votre PDF est lisible et complet</li>
                    </ul>
                </div>

                <div class="flex gap-4">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition text-sm">
                        S'inscrire au tournoi
                    </button>
                    <a href="{{ route('tournaments.show', $tournament) }}" 
                       class="flex-1 text-center px-4 py-2 bg-gray-400 text-white font-medium rounded-lg hover:bg-gray-500 transition text-sm">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 sticky top-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Informations du tournoi</h3>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-gray-600">Format</p>
                        <p class="font-semibold text-gray-900">{{ ucfirst($tournament->format) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Date de début</p>
                        <p class="font-semibold text-gray-900">{{ $tournament->start_date->format('d/m/Y') }}</p>
                    </div>
                    @if($tournament->max_players)
                        <div>
                            <p class="text-gray-600">Places disponibles</p>
                            <p class="font-semibold text-gray-900">{{ $tournament->max_players - $tournament->armyLists->count() }} / {{ $tournament->max_players }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-gray-600">Statut de votre liste</p>
                        <p class="font-semibold text-gray-900">En attente de validation</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const factionsData = {
        @foreach($factions as $faction)
            {{ $faction->id }}: [
                @foreach($faction->detachments as $detachment)
                    { id: '{{ $detachment->name }}', name: '{{ $detachment->name }}' },
                @endforeach
            ],
        @endforeach
    };

    const factionSelect = document.getElementById('faction_id');
    const detachmentSelect = document.getElementById('detachment');

    factionSelect.addEventListener('change', function() {
        const factionId = this.value;
        detachmentSelect.innerHTML = '<option value="">-- Sélectionner un détachement --</option>';

        if (factionId && factionsData[factionId]) {
            factionsData[factionId].forEach(detachment => {
                const option = document.createElement('option');
                option.value = detachment.name;
                option.textContent = detachment.name;
                @if(old('detachment'))
                    if (detachment.name === '{{ old('detachment') }}') {
                        option.selected = true;
                    }
                @endif
                detachmentSelect.appendChild(option);
            });
        }
    });

    if (factionSelect.value) {
        factionSelect.dispatchEvent(new Event('change'));
    }

    document.getElementById('pdf').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            document.getElementById('file-preview').classList.remove('hidden');
            document.getElementById('file-name').textContent = file.name;
            document.getElementById('file-size').textContent = formatFileSize(file.size);
        }
    });

    function clearFile() {
        document.getElementById('pdf').value = '';
        document.getElementById('file-preview').classList.add('hidden');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
</script>
@endsection
