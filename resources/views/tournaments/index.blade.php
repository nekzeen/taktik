@extends('layouts.public')

@section('title', 'Tournois')
@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md sm:rounded-lg mb-6 p-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-white">Tournois Warhammer 40,000</h1>
                        <p class="mt-1 text-red-100 text-xs">Découvrez et inscrivez-vous aux tournois à venir</p>
                    </div>
                </div>
                @auth
                    @php
                        $isSuperAdmin = auth()->user()->hasRole('super-admin');
                        $userTournamentsCount = \App\Models\Tournament::where('created_by', auth()->id())->count();
                        $canCreate = $canCreateTournament && ($isSuperAdmin || $userTournamentsCount === 0);
                    @endphp
                    @if(!$canCreateTournament)
                        <button disabled 
                                title="Vous n'avez pas la permission de créer un tournoi."
                                class="flex-shrink-0 bg-gray-400 text-white px-3 py-1.5 rounded font-medium cursor-not-allowed text-xs whitespace-nowrap self-start">
                            ➕ Créer un tournoi
                        </button>
                    @elseif($userTournamentsCount > 0 && !$isSuperAdmin)
                        <button disabled 
                                title="Vous avez déjà créé un tournoi. Vous ne pouvez en créer qu'un à la fois."
                                class="flex-shrink-0 bg-gray-400 text-white px-3 py-1.5 rounded font-medium cursor-not-allowed text-xs whitespace-nowrap self-start">
                            ➕ Créer un tournoi
                        </button>
                    @else
                        <a href="/tournaments/create" 
                           class="flex-shrink-0 bg-white text-red-600 px-3 py-1.5 rounded font-medium hover:bg-red-50 transition text-xs whitespace-nowrap self-start">
                            ➕ Créer un tournoi
                        </a>
                    @endif
                @endauth
            </div>
        </div>

        <!-- Tournaments List -->
        <div class="py-8">
    @if($tournaments->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tournaments as $tournament)
                @php
                    $isCreatedByUser = auth()->check() && $tournament->created_by === auth()->id();
                @endphp
                <div class="bg-white rounded-lg shadow-sm border-2 overflow-hidden hover:shadow-md transition
                    @if($isCreatedByUser) border-primary-500 bg-primary-50 @else border-gray-200 @endif">
                    @if($isCreatedByUser)
                        <div class="bg-primary-500 text-white px-4 py-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v4h8v-4zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                            <span class="text-sm font-semibold">Votre tournoi</span>
                        </div>
                    @endif
                    <div class="p-6 @if($isCreatedByUser) pt-4 @endif">
                        <!-- Status Badge -->
                        <div class="flex items-start justify-between mb-3 gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium flex-shrink-0
                                @if($tournament->status === 'open') bg-green-100 text-green-800
                                @elseif($tournament->status === 'in_progress') bg-blue-100 text-blue-800
                                @elseif($tournament->status === 'completed') bg-gray-100 text-gray-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                @if($tournament->status === 'open') Inscriptions ouvertes
                                @elseif($tournament->status === 'in_progress') En cours
                                @elseif($tournament->status === 'completed') Terminé
                                @else {{ ucfirst($tournament->status) }}
                                @endif
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold flex-shrink-0
                                @if($tournament->army_size === 'incursion') bg-blue-100 text-blue-800
                                @elseif($tournament->army_size === 'onslaught') bg-red-100 text-red-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ $tournament->getArmySizeLabel() }} ({{ $tournament->getArmySizePoints() }} pts)
                            </span>
                        </div>

                        <!-- Tournament Info -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $tournament->name }}</h3>
                        <p class="text-xs text-gray-500 mb-3 font-medium">
                            @if($tournament->format === 'elimination') Élimination
                            @elseif($tournament->format === 'swiss') Suisse
                            @elseif($tournament->format === 'league') Ligue
                            @else {{ ucfirst($tournament->format) }}
                            @endif
                        </p>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $tournament->description }}</p>
                        
                        <!-- Creator Info -->
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-xs text-gray-500 mb-1">Organisateur</p>
                            <p class="text-sm font-medium text-gray-900">{{ $tournament->creator->name ?? 'Inconnu' }}</p>
                        </div>

                        <!-- Date and Players -->
                        <div class="space-y-2 mb-4">
                            @if($tournament->start_date)
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $tournament->start_date->format('d/m/Y') }}
                                    @if($tournament->end_date && !$tournament->start_date->isSameDay($tournament->end_date))
                                        - {{ $tournament->end_date->format('d/m/Y') }}
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ $tournament->armyLists->count() }}
                                @if($tournament->max_players)
                                    / {{ $tournament->max_players }}
                                @endif
                                joueurs
                            </div>
                        </div>

                        <!-- Action Button -->
                        <a href="{{ route('tournaments.show', $tournament) }}" class="block w-full text-center bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition">
                            Voir les détails
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $tournaments->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun tournoi</h3>
            <p class="mt-1 text-sm text-gray-500">Aucun tournoi n'est disponible pour le moment.</p>
            @auth
                @can('manage-tournaments')
                    <div class="mt-6">
                        <a href="/admin/tournaments/create" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                            Créer un tournoi
                        </a>
                    </div>
                @endcan
            @endauth
        </div>
    @endif
        </div>
    </div>
</div>
@endsection
