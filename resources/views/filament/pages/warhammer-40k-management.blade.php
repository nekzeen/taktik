@php
    use Filament\Support\Enums\MaxWidth;
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white">
            <h2 class="text-2xl font-bold mb-2">Gestion des données Warhammer 40k</h2>
            <p class="text-blue-100">Synchronisez, téléchargez et traduisez facilement toutes vos données Warhammer 40k</p>
        </div>

        <!-- Grille d'actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Synchronisation Wahapedia -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center mb-4">
                    <div class="text-3xl mr-3"></div>
                    <h3 class="text-lg font-bold text-gray-800">Synchroniser depuis Wahapedia</h3>
                </div>
                <p class="text-gray-600 mb-4">Récupérez les dernières données depuis Wahapedia et fusionnez-les avec vos données existantes.</p>
                <div class="space-y-2 text-sm text-gray-700 mb-4">
                    <p>✅ Données toujours à jour</p>
                    <p>✅ Source officielle</p>
                    <p>✅ Conserve vos modifications</p>
                </div>
                <button 
                    type="button"
                    @click="$dispatch('open-modal', { id: 'sync-modal' })"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition"
                >
                    Synchroniser
                </button>
            </div>

            <!-- Téléchargement d'images -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center mb-4">
                    <div class="text-3xl mr-3"></div>
                    <h3 class="text-lg font-bold text-gray-800">Télécharger les images</h3>
                </div>
                <p class="text-gray-600 mb-4">Téléchargez toutes les images des cartes de déploiement depuis Wahapedia.</p>
                <div class="space-y-2 text-sm text-gray-700 mb-4">
                    <p>✅ Strike Force</p>
                    <p>✅ Incursions</p>
                    <p>✅ Guerre Asymétrique</p>
                </div>
                <button 
                    type="button"
                    @click="$dispatch('open-modal', { id: 'download-modal' })"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition"
                >
                    Télécharger
                </button>
            </div>

            <!-- Traduction -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center mb-4">
                    <div class="text-3xl mr-3"></div>
                    <h3 class="text-lg font-bold text-gray-800">Traduire les données</h3>
                </div>
                <p class="text-gray-600 mb-4">Traduisez automatiquement toutes vos données via DeepL.</p>
                <div class="space-y-2 text-sm text-gray-700 mb-4">
                    <p>✅ Français</p>
                    <p>✅ Allemand, Espagnol, Italien</p>
                    <p>✅ Traduction automatique</p>
                </div>
                <button 
                    type="button"
                    @click="$dispatch('open-modal', { id: 'translate-modal' })"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition"
                >
                    Traduire
                </button>
            </div>

            <!-- Synchronisation Péripéties Wahapedia -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center mb-4">
                    <div class="text-3xl mr-3"></div>
                    <h3 class="text-lg font-bold text-gray-800">Synchroniser Péripéties</h3>
                </div>
                <p class="text-gray-600 mb-4">Scrapez et synchronisez les péripéties (Twist Deck) depuis Wahapedia.</p>
                <div class="space-y-2 text-sm text-gray-700 mb-4">
                    <p>✅ 9 péripéties officielles</p>
                    <p>✅ Génération XML automatique</p>
                    <p>✅ Import direct en base</p>
                </div>
                <button 
                    type="button"
                    @click="$dispatch('open-modal', { id: 'twist-sync-modal' })"
                    class="w-full bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded transition"
                >
                    Synchroniser Péripéties
                </button>
            </div>
        </div>

        <!-- Informations -->
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6">
            <h3 class="text-lg font-bold text-blue-900 mb-2">Conseils</h3>
            <ul class="text-blue-800 space-y-2">
                <li>✅ <strong>Commencez par synchroniser</strong> depuis Wahapedia pour obtenir les dernières données</li>
                <li>✅ <strong>Téléchargez les images</strong> pour les cartes de déploiement</li>
                <li>✅ <strong>Traduisez</strong> pour avoir les données en français</li>
                <li>✅ <strong>Modifiez manuellement</strong> dans l'interface admin si nécessaire</li>
            </ul>
        </div>

        <!-- Workflow recommandé -->
        <div class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-6">
            <h3 class="text-lg font-bold text-purple-900 mb-3">Workflow recommandé</h3>
            <div class="space-y-2 text-purple-800">
                <p><strong>1.</strong> Cliquez sur "Synchroniser" → Sélectionnez "Toutes les données" → Mode "Fusionner"</p>
                <p><strong>2.</strong> Cliquez sur "Télécharger" → Sélectionnez le type de cartes</p>
                <p><strong>3.</strong> Cliquez sur "Traduire" → Sélectionnez le type et la langue</p>
                <p><strong>4.</strong> Vérifiez dans les listes Warhammer 40k</p>
            </div>
        </div>

        <!-- Listes rapides -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Accès rapide aux listes</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('filament.admin.resources.primary-missions.index') }}" class="block p-4 border rounded hover:bg-gray-50 transition">
                    <p class="font-bold text-gray-800">Missions Primaires</p>
                    <p class="text-sm text-gray-600">Gérer les missions primaires</p>
                </a>
                <a href="{{ route('filament.admin.resources.secondary-missions.index') }}" class="block p-4 border rounded hover:bg-gray-50 transition">
                    <p class="font-bold text-gray-800">Missions Secondaires</p>
                    <p class="text-sm text-gray-600">Gérer les missions secondaires</p>
                </a>
                <a href="{{ route('filament.admin.resources.twist-missions.index') }}" class="block p-4 border rounded hover:bg-gray-50 transition">
                    <p class="font-bold text-gray-800">Péripéties</p>
                    <p class="text-sm text-gray-600">Gérer les péripéties</p>
                </a>
                <a href="{{ route('filament.admin.resources.asymmetric-primary-missions.index') }}" class="block p-4 border rounded hover:bg-gray-50 transition">
                    <p class="font-bold text-gray-800">Missions Asymétriques</p>
                    <p class="text-sm text-gray-600">Gérer les missions asymétriques</p>
                </a>
                <a href="{{ route('filament.admin.resources.strike-force-deployment-cards.index') }}" class="block p-4 border rounded hover:bg-gray-50 transition">
                    <p class="font-bold text-gray-800">Strike Force</p>
                    <p class="text-sm text-gray-600">Gérer les cartes Strike Force</p>
                </a>
                <a href="{{ route('filament.admin.resources.incursion-deployment-cards.index') }}" class="block p-4 border rounded hover:bg-gray-50 transition">
                    <p class="font-bold text-gray-800">Incursions</p>
                    <p class="text-sm text-gray-600">Gérer les cartes Incursions</p>
                </a>
                <a href="{{ route('filament.admin.resources.asymmetric-warfare-deployment-cards.index') }}" class="block p-4 border rounded hover:bg-gray-50 transition">
                    <p class="font-bold text-gray-800">Guerre Asymétrique</p>
                    <p class="text-sm text-gray-600">Gérer les cartes Guerre Asymétrique</p>
                </a>
            </div>
        </div>
    </div>
</x-filament-panels::page>
