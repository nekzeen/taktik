@extends('layouts.public')

@section('title', $tournament->name)

@section('content')
<!-- Page Header -->
<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('tournaments.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium mb-2 inline-block">
                    ← Retour aux tournois
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ $tournament->name }}</h1>
                <div class="mt-2 flex items-center space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
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
                    <span class="text-gray-500">Format: {{ ucfirst($tournament->format) }}</span>
                </div>
            </div>
            @auth
                <div class="flex gap-2 flex-wrap">
                    @can('update', $tournament)
                        <a href="{{ route('tournaments.edit', $tournament) }}" 
                           class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition text-sm">
                            Éditer
                        </a>
                        <a href="{{ route('tournaments.registrations.manage', $tournament) }}" 
                           class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition text-sm">
                            Gérer les inscriptions
                        </a>
                        <form action="{{ route('tournaments.generate-matches', $tournament) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition text-sm">
                                Générer les matchs
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
                                        class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition text-sm">
                                    Fermer le tournoi
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
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition text-sm">
                                Supprimer le tournoi
                            </button>
                        </form>
                    @endcan
                </div>
                @if($tournament->status === 'open')
                    @php
                        $isRegistered = $tournament->armyLists->where('user_id', auth()->id())->isNotEmpty();
                    @endphp
                    
                    @if($isRegistered)
                        <div class="flex gap-2 mt-4">
                            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-lg font-medium text-sm">
                                Inscrit
                            </span>
                            @php
                                $userArmyList = $tournament->armyLists->where('user_id', auth()->id())->first();
                            @endphp
                            @if($userArmyList)
                                @if(!auth()->user()->hasRole('player'))
                                    <a href="{{ route('tournaments.army-list.edit', [$tournament, $userArmyList]) }}" 
                                       class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition text-sm">
                                        Modifier ma liste
                                    </a>
                                @endif
                                
                                @if($userArmyList->status !== 'validated')
                                    <form action="{{ route('tournaments.unregister', $tournament) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir vous désinscrire de ce tournoi ? Votre liste d\'armée sera supprimée.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition text-sm">
                                            Se désinscrire
                                        </button>
                                    </form>
                                @else
                                    <button type="button" 
                                            disabled
                                            title="Vous ne pouvez pas vous désinscrire car votre liste a été validée"
                                            class="bg-gray-400 text-white px-4 py-2 rounded-lg font-medium cursor-not-allowed text-sm">
                                        Se désinscrire
                                    </button>
                                @endif
                            @endif
                        </div>
                    @else
                        <a href="{{ route('tournaments.register.form', $tournament) }}" 
                           class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition text-sm">
                            S'inscrire au tournoi
                        </a>
                    @endif
                @endif
            @else
                <a href="{{ route('login') }}" class="bg-primary-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-primary-700 transition">
                    Se connecter pour s'inscrire
                </a>
            @endauth
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
                <div style="background: linear-gradient(to right, #b91c1c, #991b1b); padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 4px solid #7f1d1d;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <h2 style="color: #ffffff; font-size: 2rem; font-weight: 800; margin-bottom: 0.75rem; line-height: 1.2;">
                                Matchs du tournoi
                            </h2>
                            <p style="color: #fecaca; font-size: 1.125rem; font-weight: 500;">
                                {{ $tournament->tournamentMatches->count() }} matchs programmés
                            </p>
                        </div>
                        <a href="{{ route('tournaments.matches.index', $tournament) }}" 
                           style="background-color: #ffffff; color: #991b1b; padding: 1rem 2rem; border-radius: 0.75rem; font-weight: 700; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 2px solid #fecaca; display: inline-block; transition: all 0.2s;"
                           onmouseover="this.style.backgroundColor='#fef2f2'"
                           onmouseout="this.style.backgroundColor='#ffffff'">
                            Voir tous les matchs →
                        </a>
                    </div>
                </div>
            @endif

            <!-- Classement -->
            @if(count($rankings) > 0 && $tournament->tournamentMatches->where('status', 'completed')->count() > 0)
                <div style="background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 4px solid #7f1d1d;">
                    <h2 style="color: #ffffff; font-size: 2rem; font-weight: 800; margin-bottom: 1.5rem; line-height: 1.2;">
                        🏆 Classement
                    </h2>
                    
                    <div style="background-color: #ffffff; border-radius: 0.75rem; overflow: hidden;">
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
                                        <td style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">{{ $player['points'] }}</td>
                                        <td style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">0</td>
                                        <td style="padding: 1rem; text-align: center; font-weight: 700; color: @if($player['points'] > 0) #059669 @elseif($player['points'] < 0) #dc2626 @else #6b7280 @endif">
                                            @if($player['points'] > 0)+@endif{{ $player['points'] }}
                                        </td>
                                        <td style="padding: 1rem; text-align: center;">
                                            <span style="background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%); color: #ffffff; padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-weight: 700; font-size: 1.125rem;">
                                                {{ $player['points'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 1rem; padding: 1rem; background-color: rgba(255, 255, 255, 0.1); border-radius: 0.5rem;">
                        <div style="color: #fecaca; font-size: 0.875rem; line-height: 1.5;">
                            <strong style="color: #ffffff;">Légende :</strong> J = Joués • V = Victoires • N = Nuls • D = Défaites • PV+ = Points de Victoire marqués • PV- = Points de Victoire encaissés • Diff = Différence • Pts = Points (3 pts victoire, 1 pt nul)
                        </div>
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
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                            <span class="text-primary-600 font-semibold">
                                                {{ substr($armyList->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $armyList->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $armyList->faction->name ?? 'Faction non spécifiée' }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($armyList->status === 'validated') bg-green-100 text-green-800
                                    @elseif($armyList->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($armyList->status) }}
                                </span>
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
