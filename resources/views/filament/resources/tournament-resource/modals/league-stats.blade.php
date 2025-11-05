<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-blue-50 dark:bg-blue-950 rounded-lg p-4 border-2 border-blue-200 dark:border-blue-800">
            <div class="text-sm font-medium text-blue-800 dark:text-blue-300">Joueurs inscrits</div>
            <div class="text-2xl font-bold text-blue-900 dark:text-blue-200 mt-1">{{ $stats['players'] }}</div>
        </div>
        
        <div class="bg-purple-50 dark:bg-purple-950 rounded-lg p-4 border-2 border-purple-200 dark:border-purple-800">
            <div class="text-sm font-medium text-purple-800 dark:text-purple-300">Matchs nécessaires</div>
            <div class="text-2xl font-bold text-purple-900 dark:text-purple-200 mt-1">{{ $stats['total_matches_needed'] }}</div>
            <div class="text-xs text-purple-700 dark:text-purple-400 mt-1">Pour une ligue complète</div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-green-50 dark:bg-green-950 rounded-lg p-4 border-2 border-green-200 dark:border-green-800">
            <div class="text-sm font-medium text-green-800 dark:text-green-300">Matchs créés</div>
            <div class="text-2xl font-bold text-green-900 dark:text-green-200 mt-1">{{ $stats['matches_created'] }}</div>
        </div>
        
        <div class="bg-emerald-50 dark:bg-emerald-950 rounded-lg p-4 border-2 border-emerald-200 dark:border-emerald-800">
            <div class="text-sm font-medium text-emerald-800 dark:text-emerald-300">Matchs terminés</div>
            <div class="text-2xl font-bold text-emerald-900 dark:text-emerald-200 mt-1">{{ $stats['matches_completed'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-yellow-50 dark:bg-yellow-950 rounded-lg p-4 border-2 border-yellow-200 dark:border-yellow-800">
            <div class="text-sm font-medium text-yellow-800 dark:text-yellow-300">En attente</div>
            <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-200 mt-1">{{ $stats['matches_pending'] }}</div>
        </div>
        
        <div class="bg-orange-50 dark:bg-orange-950 rounded-lg p-4 border-2 border-orange-200 dark:border-orange-800">
            <div class="text-sm font-medium text-orange-800 dark:text-orange-300">En cours</div>
            <div class="text-2xl font-bold text-orange-900 dark:text-orange-200 mt-1">{{ $stats['matches_in_progress'] }}</div>
        </div>
    </div>

    @if($stats['matches_missing'] > 0)
        <div class="bg-red-50 dark:bg-red-950 rounded-lg p-4 border-2 border-red-200 dark:border-red-800">
            <div class="text-sm font-medium text-red-800 dark:text-red-300">Matchs manquants</div>
            <div class="text-2xl font-bold text-red-900 dark:text-red-200 mt-1">{{ $stats['matches_missing'] }}</div>
            <div class="text-xs text-red-700 dark:text-red-400 mt-1">Utilisez "Générer matchs de ligue" pour les créer</div>
        </div>
    @endif

    <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-lg p-6 border-4 border-primary-800">
        <div class="text-center">
            <div class="text-sm font-medium text-white mb-2">Progression de la ligue</div>
            <div class="text-4xl font-bold text-white">{{ $stats['completion_percentage'] }}%</div>
            <div class="mt-3 bg-white/20 rounded-full h-3 overflow-hidden">
                <div class="bg-white h-full rounded-full transition-all duration-500" style="width: {{ $stats['completion_percentage'] }}%"></div>
            </div>
            <div class="text-xs text-primary-100 mt-2">
                {{ $stats['matches_completed'] }} / {{ $stats['total_matches_needed'] }} matchs terminés
            </div>
        </div>
    </div>

    @if($stats['players'] >= 2)
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border-2 border-gray-300 dark:border-gray-600">
            <div class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-2">Informations</div>
            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                <li>• Chaque joueur doit affronter tous les autres joueurs une fois</li>
                <li>• Formule: n × (n-1) ÷ 2 matchs (où n = nombre de joueurs)</li>
                <li>• Les matchs terminés ne seront pas modifiés lors de la génération</li>
                <li>• Vous pouvez générer les matchs manquants à tout moment</li>
            </ul>
        </div>
    @endif
</div>
