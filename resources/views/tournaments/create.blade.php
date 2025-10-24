@extends('layouts.public')

@section('title', 'Créer un tournoi')

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md sm:rounded-lg mb-6 p-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <a href="{{ route('tournaments.index') }}" class="text-white hover:text-red-100 text-xs font-medium mb-1 inline-block">
                            ← Retour aux tournois
                        </a>
                        <h1 class="text-2xl font-bold text-white">Créer un tournoi</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form method="POST" action="{{ route('tournaments.store') }}" class="space-y-6">
            @csrf

            <!-- Nom -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nom du tournoi *
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}"
                       required
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-500 @error('name') border-red-500 @enderror"
                       placeholder="Ex: Tournoi Warhammer 40K - Octobre 2025">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="4"
                          class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-500 @error('description') border-red-500 @enderror"
                          placeholder="Décrivez votre tournoi...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Format et Taille d'armée -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="format" class="block text-sm font-medium text-gray-700 mb-2">
                        Format *
                    </label>
                    <select name="format" 
                            id="format" 
                            required
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-500 @error('format') border-red-500 @enderror">
                        <option value="">-- Sélectionner un format --</option>
                        <option value="elimination" @selected(old('format') === 'elimination')>Élimination</option>
                        <option value="swiss" @selected(old('format') === 'swiss')>Suisse</option>
                        <option value="league" @selected(old('format') === 'league')>Ligue</option>
                    </select>
                    @error('format')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="army_size" class="block text-sm font-medium text-gray-700 mb-2">
                        Taille d'armée *
                    </label>
                    <select name="army_size" 
                            id="army_size" 
                            required
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-500 @error('army_size') border-red-500 @enderror">
                        <option value="">-- Sélectionner une taille --</option>
                        <option value="incursion" @selected(old('army_size') === 'incursion')>INCURSION (1000 pts)</option>
                        <option value="strike_force" @selected(old('army_size') === 'strike_force')>FORCE DE FRAPPE (2000 pts)</option>
                        <option value="onslaught" @selected(old('army_size') === 'onslaught')>OFFENSIVE (3000 pts)</option>
                    </select>
                    @error('army_size')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date de début
                    </label>
                    <input type="date" 
                           name="start_date" 
                           id="start_date" 
                           value="{{ old('start_date') }}"
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-500 @error('start_date') border-red-500 @enderror">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date de fin
                    </label>
                    <input type="date" 
                           name="end_date" 
                           id="end_date" 
                           value="{{ old('end_date') }}"
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-500 @error('end_date') border-red-500 @enderror">
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Limite de joueurs -->
            <div>
                <label for="max_players" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre maximum de joueurs
                </label>
                <input type="number" 
                       name="max_players" 
                       id="max_players" 
                       value="{{ old('max_players') }}"
                       min="2"
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-500 @error('max_players') border-red-500 @enderror"
                       placeholder="Ex: 16">
                @error('max_players')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Boutons -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition text-sm">
                    ➕ Créer le tournoi
                </button>
                <a href="{{ route('tournaments.index') }}" 
                   class="flex-1 text-center px-4 py-2 bg-gray-400 text-white font-medium rounded-lg hover:bg-gray-500 transition text-sm">
                    Annuler
                </a>
            </div>
        </form>
    </div>

        <!-- Info -->
        <div class="mt-6 bg-blue-50 border-2 border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-blue-900 mb-2">ℹ️ Informations</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Votre tournoi sera créé avec le statut "Ouvert"</li>
                <li>• Vous pourrez le modifier ou le fermer à tout moment</li>
                <li>• Les joueurs pourront s'y inscrire immédiatement</li>
            </ul>
        </div>
        </div>
    </div>
</div>
@endsection
