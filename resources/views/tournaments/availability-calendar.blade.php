@extends('layouts.public')

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md sm:rounded-lg mb-6 p-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-white">Agenda des disponibilités</h1>
                        <p class="mt-1 text-red-100 text-xs">{{ $tournament->name }} • <span class="font-semibold text-white">{{ $allAvailabilities->count() }}</span> disponibilité(s)</p>
                    </div>
                </div>
                <a href="{{ route('tournaments.matches.index', $tournament) }}" 
                   class="bg-white text-red-600 px-3 py-1.5 rounded font-medium hover:bg-red-50 transition text-xs whitespace-nowrap self-start">
                    ← Retour
                </a>
            </div>
        </div>

            @if($allAvailabilities->isEmpty())
                <!-- Message si aucune disponibilité -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune disponibilité définie</h3>
                        <p class="text-gray-600 mb-6">Les joueurs n'ont pas encore défini leurs disponibilités pour ce tournoi.</p>
                        <a href="{{ route('tournaments.matches.index', $tournament) }}" 
                           class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition">
                            Retour aux matchs
                        </a>
                    </div>
                </div>
            @else
                <!-- Disponibilités ponctuelles -->
                @if($singleAvailabilities->count() > 0)
                    <div class="mb-6">
                        <div class="bg-gradient-to-r from-red-600 to-red-700 px-4 py-3 rounded-lg mb-4 shadow-sm">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Disponibilités ponctuelles</span>
                                <span class="bg-white bg-opacity-20 text-white px-3 py-1 rounded-full text-sm font-semibold ml-auto">
                                    {{ $singleAvailabilities->count() }}
                                </span>
                            </h3>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach($singleAvailabilities as $availability)
                                <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-200 hover:border-red-600 hover:shadow-md transition-all">
                                    <!-- En-tête avec nom du joueur -->
                                    <div class="bg-gradient-to-r from-red-50 to-red-100 px-3 py-3 border-b border-red-200">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-gradient-to-br from-red-600 to-red-800 rounded-full flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                {{ strtoupper(substr($availability->user->name, 0, 1)) }}
                                            </div>
                                            <h4 class="font-bold text-gray-900 text-sm truncate">{{ $availability->user->name }}</h4>
                                        </div>
                                    </div>

                                    <!-- Détails de la disponibilité -->
                                    <div class="p-3">
                                        <div class="flex items-center gap-2 mb-3 p-2 bg-yellow-50 rounded border border-yellow-200">
                                            <svg class="w-5 h-5 text-yellow-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="font-bold text-yellow-900 text-sm">
                                                {{ $availability->available_at->format('d/m/Y à H:i') }}
                                            </span>
                                        </div>

                                        @if($availability->notes)
                                            <div class="mt-3 p-2 bg-gray-50 rounded border border-gray-200">
                                                <p class="text-xs text-gray-700">
                                                    <span class="font-semibold">💬</span> {{ $availability->notes }}
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Temps restant -->
                                        <div class="mt-3 text-xs text-gray-600 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Dans {{ $availability->available_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Disponibilités sur période -->
                @if($periodAvailabilities->count() > 0)
                    <div class="mb-6">
                        <div class="bg-gradient-to-r from-green-600 to-green-700 px-4 py-3 rounded-lg mb-4 shadow-sm">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Disponibilités sur période</span>
                                <span class="bg-white bg-opacity-20 text-white px-3 py-1 rounded-full text-sm font-semibold ml-auto">
                                    {{ $periodAvailabilities->count() }}
                                </span>
                            </h3>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach($periodAvailabilities as $availability)
                                <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-200 hover:border-green-600 hover:shadow-md transition-all">
                                    <!-- En-tête avec nom du joueur -->
                                    <div class="bg-gradient-to-r from-green-50 to-green-100 px-3 py-3 border-b border-green-200">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-gradient-to-br from-green-600 to-green-800 rounded-full flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                {{ strtoupper(substr($availability->user->name, 0, 1)) }}
                                            </div>
                                            <h4 class="font-bold text-gray-900 text-sm truncate">{{ $availability->user->name }}</h4>
                                        </div>
                                    </div>

                                    <!-- Détails de la disponibilité -->
                                    <div class="p-3">
                                        <div class="space-y-2 mb-3">
                                            <div class="flex items-center gap-2 p-2 bg-green-50 rounded border border-green-200">
                                                <svg class="w-4 h-4 text-green-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                                </svg>
                                                <span class="text-xs text-green-900 font-bold">
                                                    {{ $availability->available_from->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 p-2 bg-green-50 rounded border border-green-200">
                                                <svg class="w-4 h-4 text-green-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                </svg>
                                                <span class="text-xs text-green-900 font-bold">
                                                    {{ $availability->available_to->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Durée -->
                                        <div class="mb-3 p-2 bg-green-100 rounded border border-green-300 text-center">
                                            <span class="text-xs font-bold text-green-900">
                                                ⏱️ {{ $availability->available_from->diffInDays($availability->available_to) }} jour(s)
                                            </span>
                                        </div>

                                        @if($availability->notes)
                                            <div class="mt-3 p-2 bg-gray-50 rounded border border-gray-200">
                                                <p class="text-xs text-gray-700">
                                                    <span class="font-semibold">💬</span> {{ $availability->notes }}
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Statut -->
                                        @if($availability->available_from <= now() && $availability->available_to >= now())
                                            <div class="mt-3 text-xs font-bold text-green-700 flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Disponible actuellement
                                            </div>
                                        @else
                                            <div class="mt-3 text-xs text-gray-600 flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Commence {{ $availability->available_from->diffForHumans() }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
