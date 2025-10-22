<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Warhammer 40k Tournament') }} - @yield('title', 'Accueil')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-300">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo et navigation principale -->
                <div class="flex">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <svg class="h-8 w-8 text-primary-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                        </svg>
                        <span class="ml-2 text-xl font-bold text-gray-900">WH40k Tournament</span>
                    </a>
                    <div class="hidden md:ml-10 md:flex md:items-center md:space-x-6">
                        <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 text-sm font-medium {{ request()->routeIs('home') ? 'text-primary-600' : '' }}">
                            Accueil
                        </a>
                        <a href="{{ route('tournaments.index') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 text-sm font-medium {{ request()->routeIs('tournaments.*') ? 'text-primary-600' : '' }}">
                            Tournois
                        </a>
                        @auth
                            <a href="{{ route('player-matches.index') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 text-sm font-medium {{ request()->routeIs('player-matches.*') ? 'text-primary-600' : '' }}">
                                Matchs
                            </a>
                        @endauth
                        <a href="{{ route('rankings') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 text-sm font-medium {{ request()->routeIs('rankings') ? 'text-primary-600' : '' }}">
                            Classements
                        </a>
                    </div>
                </div>

                <!-- Navigation utilisateur -->
                <div class="flex items-center space-x-4">
                    @auth
                        <!-- Menu utilisateur connecté -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center text-gray-700 hover:text-gray-900 focus:outline-none">
                                <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                                <svg class="ml-1 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mon Profil</a>
                                @can('access-admin')
                                    <a href="/admin" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Administration</a>
                                @endcan
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Boutons connexion/inscription -->
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 text-sm font-medium">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition">
                            S'inscrire
                        </a>
                    @endauth

                    <!-- Menu mobile -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700 hover:text-gray-900">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Menu mobile -->
            <div x-show="mobileMenuOpen" x-cloak class="md:hidden py-4 border-t border-gray-200">
                <a href="{{ route('home') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Accueil</a>
                <a href="{{ route('tournaments.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Tournois</a>
                @auth
                    <a href="{{ route('player-matches.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Matchs</a>
                @endauth
                <a href="{{ route('rankings') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Classements</a>
            </div>
        </nav>
    </header>

    <!-- Contenu principal -->
    <main class="min-h-screen">
        <!-- Messages flash -->
        @if (session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg" role="alert">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg" role="alert">
                    <p class="font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- À propos -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">À propos</h3>
                    <p class="text-gray-600 text-sm">
                        Plateforme de gestion de tournois Warhammer 40,000. Organisez, participez et suivez vos tournois préférés.
                    </p>
                </div>

                <!-- Liens rapides -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Liens rapides</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('tournaments.index') }}" class="text-gray-600 hover:text-primary-600 text-sm">Tournois</a></li>
                        @auth
                            <li><a href="{{ route('player-matches.index') }}" class="text-gray-600 hover:text-primary-600 text-sm">Matchs</a></li>
                        @endauth
                        <li><a href="{{ route('rankings') }}" class="text-gray-600 hover:text-primary-600 text-sm">Classements</a></li>
                        @auth
                            <li><a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-primary-600 text-sm">Mon Profil</a></li>
                        @endauth
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Contact</h3>
                    <p class="text-gray-600 text-sm">
                        Des questions ? Contactez-nous à<br>
                        <a href="mailto:contact@wh40k-tournament.fr" class="text-primary-600 hover:text-primary-700">contact@wh40k-tournament.fr</a>
                    </p>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-200">
                <p class="text-center text-gray-500 text-sm">
                    © {{ date('Y') }} Warhammer 40k Tournament. Tous droits réservés.
                </p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
