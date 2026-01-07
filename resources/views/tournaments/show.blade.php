@extends('layouts.public')

@section('title', $tournament->name)

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
                        <h1 class="text-2xl font-bold text-white">{{ $tournament->name }}</h1>
                        <div class="mt-1 flex items-center space-x-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
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
                            <span class="text-red-100 text-xs">Format: {{ ucfirst($tournament->format) }}</span>
                        </div>
                    </div>
                </div>
                @auth
                    <div class="flex gap-1.5 flex-wrap items-center">
                        @can('update', $tournament)
                            <a href="{{ route('tournaments.edit', $tournament) }}" 
                               class="bg-white text-red-600 px-3 py-1.5 rounded font-medium hover:bg-red-50 transition text-xs">
                                Éditer
                            </a>
                            <a href="{{ route('tournaments.registrations.manage', $tournament) }}" 
                               class="bg-white text-red-600 px-3 py-1.5 rounded font-medium hover:bg-red-50 transition text-xs">
                                Gérer inscriptions
                            </a>
                            <form action="{{ route('tournaments.generate-matches', $tournament) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-white text-red-600 px-3 py-1.5 rounded font-medium hover:bg-red-50 transition text-xs">
                                    Générer matchs
                                </button>
                            </form>
                        @endcan
                    @can('delete', $tournament)
                        @if($tournament->status !== 'completed' && $tournament->status !== 'cancelled')
                            <form action="{{ route('tournaments.close', $tournament) }}" 
                                  method="POST" 
                                  class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="bg-red-600 text-white px-3 py-1.5 rounded font-medium hover:bg-red-700 transition text-xs">
                                    Fermer
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('tournaments.destroy', $tournament) }}" 
                              method="POST" 
                              onsubmit="return confirm('Étes-vous sûr de vouloir supprimer ce tournoi ? Cette action est irréversible.');" 
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-600 text-white px-3 py-1.5 rounded font-medium hover:bg-red-700 transition text-xs">
                                Supprimer
                            </button>
                        </form>
                    @endcan
                </div>
                @if($tournament->status === 'open')
                    @php
                        $isRegistered = $tournament->armyLists->where('user_id', auth()->id())->isNotEmpty();
                    @endphp
                    
                    @if($isRegistered)
                        <div class="flex gap-1.5">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded font-medium text-xs">
                                Inscrit
                            </span>
                            @php
                                $userArmyList = $tournament->armyLists->where('user_id', auth()->id())->first();
                            @endphp
                            @if($userArmyList)
                                @if(!auth()->user()->hasRole('player'))
                                    <a href="{{ route('tournaments.army-list.edit', [$tournament, $userArmyList]) }}" 
                                       class="bg-red-600 text-white px-3 py-1.5 rounded font-medium hover:bg-red-700 transition text-xs">
                                        Modifier
                                    </a>
                                @endif
                                
                                @if($userArmyList->status !== 'validated')
                                    <form action="{{ route('tournaments.unregister', $tournament) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir vous désinscrire de ce tournoi ? Votre liste d\'armée sera supprimée.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-white text-red-600 px-3 py-1.5 rounded font-medium hover:bg-red-50 transition text-xs">
                                            Désinscrire
                                        </button>
                                    </form>
                                @else
                                    <button type="button" 
                                            disabled
                                            title="Vous ne pouvez pas vous désinscrire car votre liste a été validée"
                                            class="bg-gray-400 text-white px-3 py-1.5 rounded font-medium cursor-not-allowed text-xs">
                                        Désinscrire
                                    </button>
                                @endif
                            @endif
                        </div>
                    @else
                        <a href="{{ route('tournaments.register.form', $tournament) }}" 
                           class="bg-white text-red-600 px-3 py-1.5 rounded font-bold hover:bg-red-50 transition text-xs shadow-md self-start whitespace-nowrap">
                            S'inscrire
                        </a>
                    @endif
                @endif
                @else
                    <a href="{{ route('login') }}" class="bg-white text-red-600 px-3 py-1.5 rounded font-medium hover:bg-red-50 transition text-xs">
                        Se connecter
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>

<!-- Tournament Details -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Matchs -->
            @if($tournament->tournamentMatches->count() > 0)
                <div style="background: linear-gradient(to right, #b91c1c, #991b1b); padding: 1rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                        <div>
                            <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; margin-bottom: 0.25rem; line-height: 1.2;">
                                Matchs du tournoi
                            </h2>
                            <p style="color: #fecaca; font-size: 0.875rem; font-weight: 500;">
                                {{ $tournament->tournamentMatches->count() }} matchs programmés
                            </p>
                        </div>
                        <a href="{{ route('tournaments.matches.index', $tournament) }}" 
                           style="background-color: #ffffff; color: #991b1b; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 600; font-size: 0.875rem; text-decoration: none; box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1); display: inline-block; transition: all 0.2s; white-space: nowrap;"
                           onmouseover="this.style.backgroundColor='#fef2f2'"
                           onmouseout="this.style.backgroundColor='#ffffff'">
                            Voir tous les matchs →
                        </a>
                    </div>
                </div>
            @endif

            <!-- Classement -->
            @if(count($rankings) > 0 && $tournament->tournamentMatches->where('status', 'completed')->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        Classement
                    </h2>
                    
                    <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: linear-gradient(to right, #fee2e2, #fecaca); border-bottom: 2px solid #fca5a5;">
                                    <th style="padding: 1rem; text-align: left; font-weight: 700; color: #991b1b; font-size: 0.875rem;">#</th>
                                    <th style="padding: 1rem; text-align: left; font-weight: 700; color: #991b1b; font-size: 0.875rem;">Joueur</th>
                                    <th style="padding: 1rem; text-align: center; font-weight: 700; color: #991b1b; font-size: 0.875rem;">J</th>
                                    <th style="padding: 1rem; text-align: center; font-weight: 700; color: #991b1b; font-size: 0.875rem;">V</th>
                                    <th style="padding: 1rem; text-align: center; font-weight: 700; color: #991b1b; font-size: 0.875rem;">N</th>
                                    <th style="padding: 1rem; text-align: center; font-weight: 700; color: #991b1b; font-size: 0.875rem;">D</th>
                                    <th style="padding: 1rem; text-align: center; font-weight: 700; color: #991b1b; font-size: 0.875rem;">PV+</th>
                                    <th style="padding: 1rem; text-align: center; font-weight: 700; color: #991b1b; font-size: 0.875rem;">PV-</th>
                                    <th style="padding: 1rem; text-align: center; font-weight: 700; color: #991b1b; font-size: 0.875rem;">Diff</th>
                                    <th style="padding: 1rem; text-align: center; font-weight: 700; color: #991b1b; font-size: 0.875rem;">Pts</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rankings as $index => $player)
                                    <tr style="border-bottom: 1px solid #e5e7eb; @if($index < 3) background-color: #fef3c7; @endif">
                                        <td style="padding: 1rem; text-align: left;">
                                            <span style="font-weight: 700; font-size: 1.125rem; color: #111827;">
                                                @if($index === 0) 🥇
                                                @elseif($index === 1) 🥈
                                                @elseif($index === 2) 🥉
                                                @else {{ $index + 1 }}
                                                @endif
                                            </span>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <div style="font-weight: 600; color: #111827; margin-bottom: 0.25rem;">
                                                {{ $player['user']->name }}
                                            </div>
                                            @if($player['faction'])
                                                <div style="font-size: 0.75rem; color: #6b7280;">
                                                    {{ $player['faction'] }}
                                                </div>
                                            @endif
                                        </td>
                                        <td style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">{{ $player['matches_played'] }}</td>
                                        <td style="padding: 1rem; text-align: center; font-weight: 600; color: #059669;">{{ $player['wins'] }}</td>
                                        <td style="padding: 1rem; text-align: center; font-weight: 600; color: #6b7280;">{{ $player['draws'] }}</td>
                                        <td style="padding: 1rem; text-align: center; font-weight: 600; color: #dc2626;">{{ $player['losses'] }}</td>
                                        <td style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">{{ $player['pv_scored'] }}</td>
                                        <td style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">{{ $player['pv_conceded'] }}</td>
                                        <td style="padding: 1rem; text-align: center; font-weight: 700; color: @if(($player['pv_scored'] - $player['pv_conceded']) > 0) #059669 @elseif(($player['pv_scored'] - $player['pv_conceded']) < 0) #dc2626 @else #6b7280 @endif">
                                            @if(($player['pv_scored'] - $player['pv_conceded']) > 0)+@endif{{ $player['pv_scored'] - $player['pv_conceded'] }}
                                        </td>
                                        <td style="padding: 1rem; text-align: center;">
                                            <span style="background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%); color: #ffffff; padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-weight: 700; font-size: 1.125rem;">
                                                {{ $player['victory_points'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    <div class="mt-4 p-4 bg-gray-50 rounded border border-gray-200">
                        <p class="text-xs text-gray-600 leading-relaxed">
                            <strong class="text-gray-900">Légende :</strong> J = Joués • V = Victoires • N = Nuls • D = Défaites • PV+ = Points de Victoire marqués • PV- = Points de Victoire encaissés • Diff = Différence • Pts = Points (3 pts victoire, 1 pt nul)
                        </p>
                    </div>
                </div>
            @endif

            <!-- Description -->
            @if($tournament->description)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Description</h2>
                    <p class="text-gray-600 whitespace-pre-line">{{ $tournament->description }}</p>
                </div>
            @endif

            <!-- Participants -->
            @if($tournament->armyLists->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        Participants ({{ $tournament->armyLists->count() }}
                        @if($tournament->max_players)
                            / {{ $tournament->max_players }}
                        @endif)
                    </h2>

                    <div class="space-y-3">
                        @foreach($tournament->armyLists as $armyList)
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                            <span class="text-primary-600 font-semibold">
                                                {{ substr($armyList->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $armyList->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $armyList->faction->name ?? 'Faction non spécifiée' }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @if($armyList->pdf_path && $armyList->status === 'validated' && auth()->check() && $userIsRegistered)
                                        <a href="{{ route('tournaments.army-list.view', [$tournament, $armyList]) }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded transition"
                                           title="Visualiser le PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Voir
                                        </a>
                                        <a href="{{ route('tournaments.army-list.download', [$tournament, $armyList]) }}" 
                                           class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded transition"
                                           title="Télécharger le PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Télécharger
                                        </a>
                                    @endif
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium max-w-full
                                        @if($armyList->status === 'validated') bg-green-100 text-green-800
                                        @elseif($armyList->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst($armyList->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Tournament Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations</h3>
                <dl class="space-y-3">
                    @if($tournament->start_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date de début</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tournament->start_date->format('d/m/Y à H:i') }}</dd>
                        </div>
                    @endif

                    @if($tournament->end_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date de fin</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tournament->end_date->format('d/m/Y à H:i') }}</dd>
                        </div>
                    @endif

                    @if($tournament->registration_deadline)
                        <div>
                            <dt class="text-sm font-medium @if($tournament->registration_deadline < now()) text-red-600 @else text-gray-500 @endif">Date limite d'inscription</dt>
                            <dd class="mt-1 text-sm @if($tournament->registration_deadline < now()) text-red-700 @else text-gray-900 @endif">{{ $tournament->registration_deadline->format('d/m/Y à H:i') }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Format</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($tournament->format) }}</dd>
                    </div>

                    @if($tournament->max_players)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nombre maximum de joueurs</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tournament->max_players }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Places restantes</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($tournament->max_players)
                                {{ $tournament->max_players - $tournament->armyLists->count() }}
                            @else
                                Illimité
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Rules -->
            @if($tournament->rules)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Règles</h3>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $tournament->rules }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
