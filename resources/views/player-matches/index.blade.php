@extends('layouts.public')

@section('title', 'Matchs entre joueurs')

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md sm:rounded-lg mb-6 p-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-white">Matchs entre joueurs</h1>
                        <p class="mt-1 text-red-100 text-xs">Proposez ou rejoignez des matchs amicaux</p>
                    </div>
                </div>
                <a href="{{ route('player-matches.create') }}" class="bg-white text-red-600 px-3 py-1.5 rounded font-medium hover:bg-red-50 transition text-xs whitespace-nowrap self-start">
                    + Proposer un match
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            ✗ {{ session('error') }}
        </div>
    @endif

    <!-- Matchs disponibles -->
    <div class="mb-12">
        <div style="background: linear-gradient(to right, #b91c1c, #991b1b); padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 2px solid #7f1d1d; margin-bottom: 1.5rem;">
            <div class="flex justify-between items-center">
                <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700;">Matchs disponibles</h2>
                <div class="flex gap-2 items-center">
                    <span style="color: #fecaca; font-size: 0.875rem; font-weight: 500;">Trier :</span>
                    <a href="{{ route('player-matches.index', ['sort' => 'date_asc']) }}" 
                       style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_asc' ? 'background-color: #ffffff; color: #991b1b;' : 'background-color: rgba(255, 255, 255, 0.2); color: #fecaca;' }} text-decoration: none;"
                       onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                       onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">📅 Plus anciens</a>
                    <a href="{{ route('player-matches.index', ['sort' => 'date_desc']) }}" 
                       style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_desc' ? 'background-color: #ffffff; color: #991b1b;' : 'background-color: rgba(255, 255, 255, 0.2); color: #fecaca;' }} text-decoration: none;"
                       onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                       onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">📅 Plus récents</a>
                </div>
            </div>
        </div>

        @if($availableMatches->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Joueur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Faction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Localisation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Disponibilité</th>
                            @auth
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Votre demande</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                            @endauth
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($availableMatches as $match)
                            @php
                                $userRequest = auth()->check() ? $match->requests()->where('requester_id', auth()->id())->first() : null;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $match->creator->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $match->type === 'competitive' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $match->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $match->faction }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $match->getLocationDisplay() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $match->getAvailabilityDisplay() }}
                                </td>
                                @auth
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($userRequest)
                                            @if($userRequest->status === 'pending')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    ⋳ En attente
                                                </span>
                                            @elseif($userRequest->status === 'accepted')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    ✓ Acceptée
                                                </span>
                                            @elseif($userRequest->status === 'rejected')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    ✗ Refusée
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('player-matches.show', $match) }}" class="text-red-600 hover:text-red-700 font-medium">Voir</a>
                                    </td>
                                @endauth
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <p class="text-gray-600">Aucun match disponible pour le moment</p>
            </div>
        @endif
    </div>

    <!-- Mes matchs proposés -->
    @if($myProposedMatches->count() > 0)
        <div class="mb-12">
            <div style="background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 2px solid #7f1d1d; margin-bottom: 1.5rem;">
                <div class="flex justify-between items-center">
                    <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700;">Mes matchs proposés</h2>
                    <div class="flex gap-2 items-center">
                        <span style="color: #fecaca; font-size: 0.875rem; font-weight: 500;">Trier :</span>
                        <a href="{{ route('player-matches.index', ['sort' => 'date_asc']) }}" 
                           style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_asc' ? 'background-color: #ffffff; color: #991b1b;' : 'background-color: rgba(255, 255, 255, 0.2); color: #fecaca;' }} text-decoration: none;"
                           onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                           onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">📅 Plus anciens</a>
                        <a href="{{ route('player-matches.index', ['sort' => 'date_desc']) }}" 
                           style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_desc' ? 'background-color: #ffffff; color: #991b1b;' : 'background-color: rgba(255, 255, 255, 0.2); color: #fecaca;' }} text-decoration: none;"
                           onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                           onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">📅 Plus récents</a>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Faction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Localisation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Disponibilité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Demandes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($myProposedMatches as $match)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $match->type === 'competitive' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $match->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $match->faction }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $match->getLocationDisplay() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $match->getAvailabilityDisplay() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $match->status === 'open' ? 'bg-blue-100 text-blue-800' : ($match->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ $match->getStatusLabel() }}
                                    </span>
                                    @if($match->status === 'open' && !$match->isAvailable())
                                        <span class="text-xs text-red-600 ml-2">Expiré</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $pendingRequests = $match->requests->where('status', 'pending')->count();
                                    @endphp
                                    @if($pendingRequests > 0)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $pendingRequests }} en attente
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <a href="{{ route('player-matches.show', $match) }}" class="text-red-600 hover:text-red-700 font-medium">Voir</a>
                                    @if($match->status === 'open')
                                        <a href="{{ route('player-matches.edit', $match) }}" class="text-blue-600 hover:text-blue-700 font-medium">Modifier</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Mes matchs confirmés -->
    @if($myConfirmedMatches->count() > 0)
        <div class="mb-12">
            <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 2px solid #065f46; margin-bottom: 1.5rem;">
                <div class="flex justify-between items-center">
                    <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700;">Mes matchs confirmés</h2>
                    <div class="flex gap-2 items-center">
                        <span style="color: #d1fae5; font-size: 0.875rem; font-weight: 500;">Trier :</span>
                        <a href="{{ route('player-matches.index', ['sort' => 'date_asc']) }}" 
                           style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_asc' ? 'background-color: #ffffff; color: #047857;' : 'background-color: rgba(255, 255, 255, 0.2); color: #d1fae5;' }} text-decoration: none;"
                           onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                           onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">📅 Plus anciens</a>
                        <a href="{{ route('player-matches.index', ['sort' => 'date_desc']) }}" 
                           style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_desc' ? 'background-color: #ffffff; color: #047857;' : 'background-color: rgba(255, 255, 255, 0.2); color: #d1fae5;' }} text-decoration: none;"
                           onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                           onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">📅 Plus récents</a>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($myConfirmedMatches as $match)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        @if(auth()->id() === $match->creator_id)
                                            vs {{ $match->opponent->name }}
                                        @else
                                            vs {{ $match->creator->name }}
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $match->getTypeLabel() }}</p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $match->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $match->getStatusLabel() }}
                                </span>
                            </div>

                            <div class="space-y-2 text-sm text-gray-600 mb-6">
                                <p>📍 {{ $match->getLocationDisplay() }}</p>
                                <p>📅 {{ $match->getAvailabilityDisplay() }}</p>
                                <p>⚔️ {{ $match->faction }}</p>
                                @if($match->status === 'completed')
                                    <p class="font-semibold text-gray-900">Score: {{ $match->creator_score }} - {{ $match->opponent_score }}</p>
                                @endif
                            </div>

                            <a href="{{ route('player-matches.show', $match) }}" class="w-full bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition text-center">
                                Voir le match
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        </div>
    </div>
</div>
@endsection
