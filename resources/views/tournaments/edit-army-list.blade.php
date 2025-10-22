<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- En-tête -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">
                                Modifier ma liste d'armée
                            </h2>
                            <p class="text-gray-600 mt-1">
                                {{ $tournament->name }}
                            </p>
                        </div>
                        <a href="{{ route('tournaments.show', $tournament) }}" 
                           class="text-red-600 hover:text-red-800 font-semibold">
                            ← Retour
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations actuelles -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Liste actuelle</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        @if($armyList->faction)
                            <div>
                                <span class="font-semibold text-gray-700">Faction:</span>
                                <span class="text-gray-900">{{ $armyList->faction }}</span>
                            </div>
                        @endif
                        @if($armyList->detachment)
                            <div>
                                <span class="font-semibold text-gray-700">Détachement:</span>
                                <span class="text-gray-900">{{ $armyList->detachment }}</span>
                            </div>
                        @endif
                        @if($armyList->points)
                            <div>
                                <span class="font-semibold text-gray-700">Points:</span>
                                <span class="text-gray-900">{{ $armyList->points }}</span>
                            </div>
                        @endif
                        <div>
                            <span class="font-semibold text-gray-700">Statut:</span>
                            <span class="px-2 py-1 rounded-full text-xs
                                @if($armyList->status === 'validated') bg-green-100 text-green-800
                                @elseif($armyList->status === 'rejected') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                @if($armyList->status === 'validated') Validée
                                @elseif($armyList->status === 'rejected') Rejetée
                                @elseif($armyList->status === 'pending') En attente
                                @else Brouillon
                                @endif
                            </span>
                        </div>
                    </div>

                    @if($armyList->pdf_path)
                        <div class="mt-4">
                            <a href="{{ Storage::url($armyList->pdf_path) }}" 
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                </svg>
                                Voir le PDF actuel
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Formulaire de modification -->
            <form method="POST" action="{{ route('tournaments.army-list.update', [$tournament, $armyList]) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                Uploader une nouvelle liste
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Uploadez un nouveau PDF pour remplacer votre liste actuelle. Le système analysera automatiquement la nouvelle liste.
                            </p>

                            <div class="mt-4">
                                <label for="army_list_pdf" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nouvelle liste d'armée (PDF) *
                                </label>
                                <input type="file" 
                                       name="army_list_pdf" 
                                       id="army_list_pdf" 
                                       accept=".pdf"
                                       required
                                       class="block w-full text-sm text-gray-900 border-2 border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-red-500">
                                <p class="mt-2 text-xs text-gray-500">
                                    PDF uniquement, taille maximale : 10 MB
                                </p>
                                @error('army_list_pdf')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Aperçu du fichier -->
                        <div id="file-preview" class="hidden">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p id="file-name" class="text-sm font-medium text-gray-900"></p>
                                        <p id="file-size" class="text-xs text-gray-500"></p>
                                    </div>
                                    <button type="button" onclick="clearFile()" class="text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Informations -->
                        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4">
                            <h4 class="font-semibold text-yellow-900 mb-2">⚠️ Important</h4>
                            <ul class="text-sm text-yellow-800 space-y-1">
                                <li>• Votre nouvelle liste remplacera l'ancienne</li>
                                <li>• Elle sera analysée automatiquement</li>
                                <li>• Le statut repassera en "En attente de validation"</li>
                                <li>• Un organisateur devra valider la nouvelle liste</li>
                            </ul>
                        </div>

                        <!-- Boutons -->
                        <div class="flex gap-4">
                            <button type="submit" 
                                    class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition text-sm">
                                Mettre à jour ma liste
                            </button>
                            <a href="{{ route('tournaments.show', $tournament) }}" 
                               class="flex-1 text-center px-4 py-2 bg-gray-400 text-white font-medium rounded-lg hover:bg-gray-500 transition text-sm">
                                Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('army_list_pdf').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('file-preview').classList.remove('hidden');
                document.getElementById('file-name').textContent = file.name;
                document.getElementById('file-size').textContent = formatFileSize(file.size);
            }
        });

        function clearFile() {
            document.getElementById('army_list_pdf').value = '';
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
</x-app-layout>
