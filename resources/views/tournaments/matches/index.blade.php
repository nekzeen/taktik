@extends('layouts.public')

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md sm:rounded-lg mb-6 p-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-white">Matchs - {{ $tournament->name }}</h1>
                        <p class="mt-1 text-red-100 text-xs">{{ $tournament->format }} • {{ $tournament->start_date->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="flex gap-1.5 items-center flex-wrap">
                    <a href="{{ route('tournaments.availability-calendar', $tournament) }}" 
                       class="px-3 py-1.5 bg-white text-red-600 rounded hover:bg-red-50 font-medium transition flex items-center gap-1.5 border border-white text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span>Agenda</span>
                    </a>
                    @auth
                        <button onclick="openPlayerAvailabilityModal()" 
                                class="px-3 py-1.5 text-red-600 bg-white rounded font-medium transition flex items-center gap-1.5 hover:bg-red-50 border border-white text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Disponibilité</span>
                        </button>
                    @endauth
                    <a href="{{ route('tournaments.show', $tournament) }}" 
                       class="bg-white text-red-600 px-3 py-1.5 rounded font-medium hover:bg-red-50 transition text-xs whitespace-nowrap">
                        ← Retour
                    </a>
                </div>
            </div>
        </div>

            <!-- Matchs par round -->
            @forelse($matches as $round => $roundMatches)
                <div class="mb-8">
                    <!-- En-tête du round -->
                    <div style="background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); padding: 0.75rem 1rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); margin-bottom: 0.75rem;">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold flex items-center" style="color: #ffffff;">
                                <span style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; border-radius: 0.375rem; padding: 0.375rem 0.75rem; margin-right: 0.75rem; font-weight: 800; border: 2px solid rgba(255, 255, 255, 0.3); font-size: 0.875rem;">
                                    Round {{ $round }}
                                </span>
                                <span style="color: #fecaca; font-size: 0.875rem; font-weight: 500;">
                                    {{ count($roundMatches) }} match{{ count($roundMatches) > 1 ? 's' : '' }}
                                </span>
                            </h3>
                        </div>
                    </div>

                    <!-- Grille des matchs -->
                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach($roundMatches as $match)
                                <div style="background-color: #ffffff; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08); transition: all 0.15s; border: 1px solid #e5e7eb;" 
                                     onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 3px 8px rgba(185, 28, 28, 0.12)'; this.style.borderColor='#b91c1c'"
                                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 2px rgba(0, 0, 0, 0.08)'; this.style.borderColor='#e5e7eb'">
                                    
                                    <!-- En-tête de la carte -->
                                    <div style="background: linear-gradient(to right, #fee2e2, #fecaca); padding: 0.375rem 0.625rem; border-bottom: 1px solid #fca5a5;">
                                        <div class="flex justify-between items-center">
                                            @if($match->table_number)
                                                <span style="color: #991b1b; font-weight: 700; font-size: 0.75rem;">
                                                    Table {{ $match->table_number }}
                                                </span>
                                            @else
                                                <span style="color: #991b1b; font-weight: 700; font-size: 0.75rem;">Match #{{ $match->id }}</span>
                                            @endif
                                            <span class="px-2 py-0.5 text-xs rounded-full font-semibold
                                                @if($match->status === 'completed') bg-green-600 text-white
                                                @elseif($match->status === 'in_progress') bg-yellow-500 text-white
                                                @else bg-gray-400 text-white
                                                @endif" style="font-size: 0.625rem;">
                                                @if($match->status === 'completed') Terminé
                                                @elseif($match->status === 'in_progress') En cours
                                                @else En attente
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Corps de la carte -->
                                    <div style="padding: 0.625rem;">
                                        <!-- Joueur 1 -->
                                        <div style="padding: 0.5rem; border-radius: 0.375rem; margin-bottom: 0.25rem; @if($match->winner_id === $match->player1_id) background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border: 1.5px solid #10b981; @else background-color: #f9fafb; border: 1px solid #e5e7eb; @endif">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1" style="min-width: 0;">
                                                    <div style="font-weight: 700; font-size: 0.8125rem; color: #111827; display: flex; align-items: center; gap: 0.25rem;">
                                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $match->player1->name }}</span>
                                                        @if($match->winner_id === $match->player1_id)
                                                            <span style="font-size: 0.875rem; flex-shrink: 0; font-weight: bold;">(Gagnant)</span>
                                                        @endif
                                                    </div>
                                                    @if($match->player1ArmyList && $match->player1ArmyList->faction)
                                                        <div style="font-size: 0.6875rem; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 0.125rem;">
                                                            {{ $match->player1ArmyList->faction->name }}
                                                        </div>
                                                        @if($match->player1ArmyList->pdf_path && $match->player1ArmyList->status === 'validated' && auth()->check() && $userIsRegistered)
                                                            <div style="margin-top: 0.25rem; display: flex; gap: 0.25rem;">
                                                                <a href="{{ route('tournaments.army-list.view', [$tournament, $match->player1ArmyList]) }}" 
                                                                   target="_blank" 
                                                                   rel="noopener noreferrer"
                                                                   class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded transition"
                                                                   title="Visualiser le PDF">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                                    </svg>
                                                                    Voir
                                                                </a>
                                                                <a href="{{ route('tournaments.army-list.download', [$tournament, $match->player1ArmyList]) }}" 
                                                                   class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded transition"
                                                                   title="Télécharger le PDF">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                                    </svg>
                                                                </a>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                                @if($match->player1_score !== null)
                                                    <div style="font-size: 1.5rem; font-weight: 800; color: #991b1b; margin-left: 0.5rem; flex-shrink: 0;">
                                                        {{ $match->player1_score }}
                                                    </div>
                                                @else
                                                    <div style="font-size: 1.5rem; font-weight: 800; color: #d1d5db; margin-left: 0.5rem; flex-shrink: 0;">-</div>
                                                @endif
                                            </div>
                                        </div>

                                        <!--  -->
                                        <div style="text-align: center; font-weight: 700; color: #991b1b; font-size: 0.75rem; margin: 0.25rem 0;"></div>

                                        <!-- Joueur 2 -->
                                        <div style="padding: 0.5rem; border-radius: 0.375rem; margin-bottom: 0.5rem; @if($match->winner_id === $match->player2_id) background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border: 1.5px solid #10b981; @else background-color: #f9fafb; border: 1px solid #e5e7eb; @endif">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1" style="min-width: 0;">
                                                    <div style="font-weight: 700; font-size: 0.8125rem; color: #111827; display: flex; align-items: center; gap: 0.25rem;">
                                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $match->player2->name }}</span>
                                                        @if($match->winner_id === $match->player2_id)
                                                            <span style="font-size: 0.875rem; flex-shrink: 0; font-weight: bold;">(Gagnant)</span>
                                                        @endif
                                                    </div>
                                                    @if($match->player2ArmyList && $match->player2ArmyList->faction)
                                                        <div style="font-size: 0.6875rem; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 0.125rem;">
                                                            {{ $match->player2ArmyList->faction->name }}
                                                        </div>
                                                        @if($match->player2ArmyList->pdf_path && $match->player2ArmyList->status === 'validated' && auth()->check() && $userIsRegistered)
                                                            <div style="margin-top: 0.25rem; display: flex; gap: 0.25rem;">
                                                                <a href="{{ route('tournaments.army-list.view', [$tournament, $match->player2ArmyList]) }}" 
                                                                   target="_blank" 
                                                                   rel="noopener noreferrer"
                                                                   class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded transition"
                                                                   title="Visualiser le PDF">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                                    </svg>
                                                                    Voir
                                                                </a>
                                                                <a href="{{ route('tournaments.army-list.download', [$tournament, $match->player2ArmyList]) }}" 
                                                                   class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded transition"
                                                                   title="Télécharger le PDF">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                                    </svg>
                                                                </a>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                                @if($match->player2_score !== null)
                                                    <div style="font-size: 1.5rem; font-weight: 800; color: #991b1b; margin-left: 0.5rem; flex-shrink: 0;">
                                                        {{ $match->player2_score }}
                                                    </div>
                                                @else
                                                    <div style="font-size: 1.5rem; font-weight: 800; color: #d1d5db; margin-left: 0.5rem; flex-shrink: 0;">-</div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Match nul -->
                                        @if($match->is_draw)
                                            <div style="text-align: center; padding: 0.25rem; background-color: #fef3c7; border-radius: 0.25rem; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.6875rem; color: #92400e;">
                                                ⚖️ Nul
                                            </div>
                                        @endif

                                        <!-- Disponibilités globales (uniquement pour matchs non terminés) -->
                                        @if($match->status !== 'completed')
                                            @php
                                                $player1Availability = $playerAvailabilities->get($match->player1_id);
                                                $player2Availability = $playerAvailabilities->get($match->player2_id);
                                                $hasAvailabilities = $player1Availability || $player2Availability;
                                            @endphp
                                            
                                            @if($hasAvailabilities)
                                                <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border: 1px solid #93c5fd; border-radius: 0.25rem; padding: 0.375rem; margin-bottom: 0.5rem;">
                                                    <div style="font-size: 0.625rem; font-weight: 700; color: #1e40af; margin-bottom: 0.125rem;">
                                                        Disponibilités
                                                    </div>
                                                    @if($player1Availability)
                                                        <div style="font-size: 0.625rem; color: #1e3a8a; display: flex; align-items: center; gap: 0.25rem; margin-bottom: 0.125rem;">
                                                            <span style="font-weight: 600;">• {{ $match->player1->name }}:</span>
                                                            <span>{{ $player1Availability->getShortFormat() }}</span>
                                                        </div>
                                                    @endif
                                                    @if($player2Availability)
                                                        <div style="font-size: 0.625rem; color: #1e3a8a; display: flex; align-items: center; gap: 0.25rem;">
                                                            <span style="font-weight: 600;">• {{ $match->player2->name }}:</span>
                                                            <span>{{ $player2Availability->getShortFormat() }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif

                                        <!-- Actions -->
                                        @auth
                                            <div class="space-y-2">
                                                @if($match->status !== 'completed')
                                                    @if($match->isSetupValid() && !$match->isScoreRecorderSelected())
                                                        <!-- Bouton pour voir la configuration (non modifiable) -->
                                                        <a href="{{ route('tournaments.matches.summary', [$tournament, $match]) }}" 
                                                           style="display: block; text-align: center; padding: 0.375rem; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: #ffffff; border-radius: 0.375rem; font-weight: 600; font-size: 0.75rem; text-decoration: none; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08); transition: all 0.15s;"
                                                           onmouseover="this.style.boxShadow='0 2px 3px rgba(0, 0, 0, 0.12)'"
                                                           onmouseout="this.style.boxShadow='0 1px 2px rgba(0, 0, 0, 0.08)'">
                                                            Voir config
                                                        </a>
                                                    @endif
                                                    
                                                    @if($match->canSelectScoreRecorder() && $match->isPlayer(auth()->user()))
                                                        <!-- Sélectionner le joueur qui saisit le score (pour les joueurs du match) -->
                                                        <button onclick="openScoreRecorderModal({{ $match->id }}, {{ $match->player1_id }}, '{{ $match->player1->name }}', {{ $match->player2_id }}, '{{ $match->player2->name }}')" 
                                                                style="display: block; width: 100%; text-align: center; padding: 0.375rem; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; border-radius: 0.375rem; font-weight: 600; font-size: 0.75rem; border: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08); transition: all 0.15s;"
                                                                onmouseover="this.style.boxShadow='0 2px 3px rgba(0, 0, 0, 0.12)'"
                                                                onmouseout="this.style.boxShadow='0 1px 2px rgba(0, 0, 0, 0.08)'">
                                                            Sélectionner joueur
                                                        </button>
                                                    @elseif($match->isScoreRecorderSelected())
                                                        <!-- Afficher le bouton "Saisir le score" si l'utilisateur est le score recorder -->
                                                        @if($match->score_recorder_id === auth()->id())
                                                            <a href="{{ route('tournaments.matches.score', [$tournament, $match]) }}" 
                                                               style="display: block; text-align: center; padding: 0.375rem; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #ffffff; border-radius: 0.375rem; font-weight: 600; font-size: 0.75rem; text-decoration: none; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08); transition: all 0.15s;"
                                                               onmouseover="this.style.boxShadow='0 2px 3px rgba(0, 0, 0, 0.12)'"
                                                               onmouseout="this.style.boxShadow='0 1px 2px rgba(0, 0, 0, 0.08)'">
                                                                Saisir le score
                                                            </a>
                                                        @elseif($match->isPlayer(auth()->user()))
                                                            <!-- Afficher le bouton "Visualiser le score" si l'utilisateur est l'autre joueur -->
                                                            <a href="{{ route('tournaments.matches.view-score', [$tournament, $match]) }}" 
                                                               style="display: block; text-align: center; padding: 0.375rem; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff; border-radius: 0.375rem; font-weight: 600; font-size: 0.75rem; text-decoration: none; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08); transition: all 0.15s;"
                                                               onmouseover="this.style.boxShadow='0 2px 3px rgba(0, 0, 0, 0.12)'"
                                                               onmouseout="this.style.boxShadow='0 1px 2px rgba(0, 0, 0, 0.08)'">
                                                                Visualiser score
                                                            </a>
                                                        @endif
                                                    @endif
                                                @endif
                                            </div>
                                        @endauth
                                    </div>
                                </div>
                            @endforeach
                        </div>
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-600">
                        <p class="text-lg">Aucun match n'a encore été créé pour ce tournoi.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

    <!-- Modale de disponibilité globale -->
    @include('tournaments.matches._player_availability_modal', ['tournament' => $tournament])

    <!-- Modale de sélection du joueur qui saisit le score -->
    <div id="scoreRecorderModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 50; flex-items: center; justify-content: center;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 0.5rem; padding: 1.5rem; max-width: 400px; width: 90%; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);">
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">Sélectionner le joueur</h3>
            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1.5rem;">Qui va saisir le score du match ?</p>
            
            <form id="scoreRecorderForm" method="POST" style="space-y: 1rem;">
                @csrf
                <input type="hidden" name="score_recorder_id" id="scoreRecorderId">
                
                <div id="playerOptions" style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
                    <!-- Les options seront ajoutées par JavaScript -->
                </div>
                
                <div style="display: flex; gap: 0.75rem;">
                    <button type="submit" style="flex: 1; padding: 0.5rem; background: #059669; color: white; border: none; border-radius: 0.375rem; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                        Confirmer
                    </button>
                    <button type="button" onclick="closeScoreRecorderModal()" style="flex: 1; padding: 0.5rem; background: #e5e7eb; color: #111827; border: none; border-radius: 0.375rem; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentMatchId = null;
        let currentTournamentId = {{ $tournament->id }};

        function openScoreRecorderModal(matchId, player1Id, player1Name, player2Id, player2Name) {
            currentMatchId = matchId;
            
            const playerOptions = document.getElementById('playerOptions');
            playerOptions.innerHTML = `
                <label style="display: flex; align-items: center; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 0.375rem; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.borderColor='#059669'; this.style.backgroundColor='#f0fdf4'" onmouseout="this.style.borderColor='#e5e7eb'; this.style.backgroundColor='white'">
                    <input type="radio" name="scoreRecorderOption" value="${player1Id}" style="margin-right: 0.75rem; cursor: pointer;">
                    <span style="font-weight: 600; color: #111827;">${player1Name}</span>
                </label>
                <label style="display: flex; align-items: center; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 0.375rem; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.borderColor='#059669'; this.style.backgroundColor='#f0fdf4'" onmouseout="this.style.borderColor='#e5e7eb'; this.style.backgroundColor='white'">
                    <input type="radio" name="scoreRecorderOption" value="${player2Id}" style="margin-right: 0.75rem; cursor: pointer;">
                    <span style="font-weight: 600; color: #111827;">${player2Name}</span>
                </label>
            `;
            
            document.getElementById('scoreRecorderModal').style.display = 'flex';
        }

        function closeScoreRecorderModal() {
            document.getElementById('scoreRecorderModal').style.display = 'none';
            currentMatchId = null;
        }

        document.getElementById('scoreRecorderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const selectedPlayer = document.querySelector('input[name="scoreRecorderOption"]:checked');
            if (!selectedPlayer) {
                alert('Veuillez sélectionner un joueur');
                return;
            }
            
            document.getElementById('scoreRecorderId').value = selectedPlayer.value;
            
            fetch(`/tournaments/${currentTournamentId}/matches/${currentMatchId}/select-score-recorder`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    score_recorder_id: selectedPlayer.value
                })
            })
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Erreur lors de la sélection du joueur');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la sélection du joueur');
            });
        });

        // Fermer la modale en cliquant en dehors
        document.getElementById('scoreRecorderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeScoreRecorderModal();
            }
        });
    </script>
@endsection
