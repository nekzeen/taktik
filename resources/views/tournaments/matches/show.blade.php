@extends('layouts.public')

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- En-tête -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 overflow-hidden shadow-md sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-white">
                                Match - Round {{ $match->round }}
                                @if($match->table_number)
                                    - Table {{ $match->table_number }}
                                @endif
                            </h2>
                            <p class="text-red-100 mt-1">
                                {{ $tournament->name }}
                            </p>
                        </div>
                        <a href="{{ route('tournaments.matches.index', $tournament) }}" 
                           class="text-white hover:text-red-100 font-semibold">
                            ← Retour aux matchs
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statut -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-semibold">Statut :</span>
                        <span class="px-4 py-2 rounded-full font-semibold
                            @if($match->status === 'completed') bg-green-100 text-green-800
                            @elseif($match->status === 'in_progress') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            @if($match->status === 'completed') ✓ Terminé
                            @elseif($match->status === 'in_progress') ⏳ En cours
                            @else ⏸ En attente
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Résultat du match -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">Résultat</h3>

                    <div class="space-y-4">
                        <!-- Joueur 1 -->
                        <div class="p-6 rounded-lg
                            @if($match->winner_id === $match->player1_id) bg-green-50 border-4 border-green-500
                            @else bg-gray-50 border-2 border-gray-200
                            @endif">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                                        {{ $match->player1->name }}
                                        @if($match->winner_id === $match->player1_id)
                                            <span class="text-3xl">🏆</span>
                                        @endif
                                    </div>
                                    @if($match->player1ArmyList)
                                        <div class="mt-2 space-y-1">
                                            @if($match->player1ArmyList->faction)
                                                <div class="text-gray-700">
                                                    <span class="font-semibold">Faction:</span> 
                                                    {{ $match->player1ArmyList->faction->name }}
                                                </div>
                                            @endif
                                            @if($match->player1ArmyList->detachment)
                                                <div class="text-gray-700">
                                                    <span class="font-semibold">Détachement:</span> 
                                                    {{ $match->player1ArmyList->detachment }}
                                                </div>
                                            @endif
                                            @if($match->player1ArmyList->points)
                                                <div class="text-gray-700">
                                                    <span class="font-semibold">Points:</span> 
                                                    {{ $match->player1ArmyList->points }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @if($match->player1_score !== null)
                                    <div class="text-right">
                                        <div class="text-5xl font-bold text-gray-900">
                                            {{ $match->player1_score }}
                                        </div>
                                        @if($match->player1_victory_points !== null)
                                            <div class="text-sm text-gray-600 mt-1">
                                                {{ $match->player1_victory_points }} PV
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-4xl text-gray-400">-</div>
                                @endif
                            </div>
                        </div>

                        <!-- VS ou Match nul -->
                        <div class="text-center py-2">
                            @if($match->is_draw)
                                <span class="text-2xl font-bold text-red-600">MATCH NUL</span>
                            @else
                                <span class="text-2xl font-bold text-gray-500">VS</span>
                            @endif
                        </div>

                        <!-- Joueur 2 -->
                        <div class="p-6 rounded-lg
                            @if($match->winner_id === $match->player2_id) bg-green-50 border-4 border-green-500
                            @else bg-gray-50 border-2 border-gray-200
                            @endif">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                                        {{ $match->player2->name }}
                                        @if($match->winner_id === $match->player2_id)
                                            <span class="text-3xl">🏆</span>
                                        @endif
                                    </div>
                                    @if($match->player2ArmyList)
                                        <div class="mt-2 space-y-1">
                                            @if($match->player2ArmyList->faction)
                                                <div class="text-gray-700">
                                                    <span class="font-semibold">Faction:</span> 
                                                    {{ $match->player2ArmyList->faction->name }}
                                                </div>
                                            @endif
                                            @if($match->player2ArmyList->detachment)
                                                <div class="text-gray-700">
                                                    <span class="font-semibold">Détachement:</span> 
                                                    {{ $match->player2ArmyList->detachment }}
                                                </div>
                                            @endif
                                            @if($match->player2ArmyList->points)
                                                <div class="text-gray-700">
                                                    <span class="font-semibold">Points:</span> 
                                                    {{ $match->player2ArmyList->points }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @if($match->player2_score !== null)
                                    <div class="text-right">
                                        <div class="text-5xl font-bold text-gray-900">
                                            {{ $match->player2_score }}
                                        </div>
                                        @if($match->player2_victory_points !== null)
                                            <div class="text-sm text-gray-600 mt-1">
                                                {{ $match->player2_victory_points }} PV
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-4xl text-gray-400">-</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($match->notes)
                        <div class="mt-6 p-4 bg-red-50 border-2 border-red-200 rounded-lg">
                            <h4 class="font-semibold text-red-900 mb-2">Notes :</h4>
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $match->notes }}</p>
                        </div>
                    @endif

                    <!-- Dates -->
                    @if($match->started_at || $match->completed_at)
                        <div class="mt-6 grid grid-cols-2 gap-4 text-sm text-gray-600">
                            @if($match->started_at)
                                <div>
                                    <span class="font-semibold">Début :</span> 
                                    {{ $match->started_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                            @if($match->completed_at)
                                <div>
                                    <span class="font-semibold">Fin :</span> 
                                    {{ $match->completed_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Action -->
                    @auth
                        @if($match->canEditResult(auth()->user()))
                            <div class="mt-6">
                                <a href="{{ route('tournaments.matches.edit', [$tournament, $match]) }}" 
                                   class="block w-full text-center px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition text-sm">
                                    @if($match->status === 'completed')
                                        Modifier le résultat
                                    @else
                                        Saisir le résultat
                                    @endif
                                </a>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
