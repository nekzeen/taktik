<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- En-tête -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">
                                Saisir le résultat
                            </h2>
                            <p class="text-gray-600 mt-1">
                                Round {{ $match->round }}
                                @if($match->table_number)
                                    - Table {{ $match->table_number }}
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('tournaments.matches.show', [$tournament, $match]) }}" 
                           class="text-red-600 hover:text-red-800 font-semibold">
                            ← Annuler
                        </a>
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <form method="POST" action="{{ route('tournaments.matches.update', [$tournament, $match]) }}">
                @csrf
                @method('PUT')

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <!-- Joueur 1 -->
                        <div class="border-2 border-red-200 rounded-lg p-6 bg-red-50">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">
                                {{ $match->player1->name }}
                            </h3>
                            @if($match->player1ArmyList)
                                <p class="text-gray-600 mb-4">
                                    {{ $match->player1ArmyList->faction ? $match->player1ArmyList->faction->name : 'Faction non spécifiée' }}
                                    @if($match->player1ArmyList->detachment)
                                        - {{ $match->player1ArmyList->detachment }}
                                    @endif
                                </p>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="player1_score" class="block text-sm font-medium text-gray-700 mb-2">
                                        Score *
                                    </label>
                                    <input type="number" 
                                           name="player1_score" 
                                           id="player1_score" 
                                           min="0"
                                           value="{{ old('player1_score', $match->player1_score) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                           required>
                                    @error('player1_score')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="player1_victory_points" class="block text-sm font-medium text-gray-700 mb-2">
                                        Points de victoire
                                    </label>
                                    <input type="number" 
                                           name="player1_victory_points" 
                                           id="player1_victory_points" 
                                           min="0"
                                           value="{{ old('player1_victory_points', $match->player1_victory_points) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    @error('player1_victory_points')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- VS -->
                        <div class="text-center text-2xl font-bold text-gray-500">VS</div>

                        <!-- Joueur 2 -->
                        <div class="border-2 border-red-200 rounded-lg p-6 bg-red-50">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">
                                {{ $match->player2->name }}
                            </h3>
                            @if($match->player2ArmyList)
                                <p class="text-gray-600 mb-4">
                                    {{ $match->player2ArmyList->faction ? $match->player2ArmyList->faction->name : 'Faction non spécifiée' }}
                                    @if($match->player2ArmyList->detachment)
                                        - {{ $match->player2ArmyList->detachment }}
                                    @endif
                                </p>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="player2_score" class="block text-sm font-medium text-gray-700 mb-2">
                                        Score *
                                    </label>
                                    <input type="number" 
                                           name="player2_score" 
                                           id="player2_score" 
                                           min="0"
                                           value="{{ old('player2_score', $match->player2_score) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                           required>
                                    @error('player2_score')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="player2_victory_points" class="block text-sm font-medium text-gray-700 mb-2">
                                        Points de victoire
                                    </label>
                                    <input type="number" 
                                           name="player2_victory_points" 
                                           id="player2_victory_points" 
                                           min="0"
                                           value="{{ old('player2_victory_points', $match->player2_victory_points) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    @error('player2_victory_points')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Match nul -->
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="is_draw" 
                                   id="is_draw" 
                                   value="1"
                                   {{ old('is_draw', $match->is_draw) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <label for="is_draw" class="ml-2 block text-sm text-gray-900">
                                Match nul
                            </label>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes (optionnel)
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="4"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                      placeholder="Remarques, incidents, etc.">{{ old('notes', $match->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Boutons -->
                        <div class="flex gap-4">
                            <button type="submit" 
                                    class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition text-sm">
                                Enregistrer le résultat
                            </button>
                            <a href="{{ route('tournaments.matches.show', [$tournament, $match]) }}" 
                               class="flex-1 text-center px-4 py-2 bg-gray-400 text-white font-medium rounded-lg hover:bg-gray-500 transition text-sm">
                                Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Aide -->
            <div class="mt-6 bg-red-50 border-2 border-red-200 rounded-lg p-4">
                <h4 class="font-semibold text-red-900 mb-2">💡 Aide</h4>
                <ul class="text-sm text-red-800 space-y-1">
                    <li>• Le vainqueur sera déterminé automatiquement en fonction des scores</li>
                    <li>• Cochez "Match nul" si les deux joueurs ont le même score</li>
                    <li>• Les points de victoire sont optionnels mais recommandés pour les départages</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
