@extends('layouts.public')

@section('title', 'Accueil')

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-r from-primary-600 to-primary-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                Bienvenue sur Taktyk
            </h1>
            <p class="text-xl md:text-2xl text-primary-100 mb-8">
                Organisez, participez et suivez vos tournois Warhammer 40,000
            </p>
            <div class="flex justify-center space-x-4 flex-wrap gap-4">
                @guest
                    <a href="{{ route('register') }}" class="bg-white text-red-600 px-8 py-3 rounded-lg font-semibold hover:bg-red-50 transition border border-red-200">
                        Créer un compte
                    </a>
                @endguest
                <a href="{{ route('tournaments.index') }}" class="bg-white text-red-600 px-8 py-3 rounded-lg font-semibold hover:bg-red-50 transition border border-red-200">
                    Voir les tournois
                </a>
                <a href="{{ route('player-matches.index') }}" class="bg-white text-red-600 px-8 py-3 rounded-lg font-semibold hover:bg-red-50 transition border border-red-200">
                    Voir les matchs
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Feature 1 -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition">
            <div class="flex items-center justify-center w-12 h-12 bg-primary-100 rounded-lg mb-4">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Inscription facile</h3>
            <p class="text-gray-600">
                Inscrivez-vous aux tournois en quelques clics et soumettez vos listes d'armées facilement.
            </p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition">
            <div class="flex items-center justify-center w-12 h-12 bg-primary-100 rounded-lg mb-4">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Suivi en temps réel</h3>
            <p class="text-gray-600">
                Suivez l'avancement des tournois, les résultats des matchs et les classements en direct.
            </p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition">
            <div class="flex items-center justify-center w-12 h-12 bg-primary-100 rounded-lg mb-4">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Classements</h3>
            <p class="text-gray-600">
                Consultez les classements généraux et suivez votre progression au fil des tournois.
            </p>
        </div>
    </div>
</div>

@endsection
