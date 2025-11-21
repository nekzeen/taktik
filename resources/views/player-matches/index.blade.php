@extends('layouts.public')

@section('title', 'Matchs entre joueurs')

@section('content')
<style>
    /* Responsive layout: table on desktop, cards on mobile */
    @media (max-width: 768px) {
        .table-container {
            display: none;
        }
        
        .cards-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
    
    @media (min-width: 769px) {
        .cards-container {
            display: none;
        }
        
        .table-container {
            display: block;
        }
    }
</style>
<div class="py-12 bg-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md sm:rounded-lg mb-6 p-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-white">Matchs entre joueurs</h1>
                        <p class="mt-1 text-red-100 text-xs">
                            @auth
                                Proposez ou rejoignez des matchs amicaux
                            @else
                                Consultez les matchs disponibles
                            @endauth
                        </p>
                    </div>
                </div>
                @auth
                    <a href="{{ route('player-matches.create') }}" class="bg-white text-red-600 px-3 py-1.5 rounded font-medium hover:bg-red-50 transition text-xs whitespace-nowrap self-start">
                        + Proposer un match
                    </a>
                @endauth
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            {{ session('success') }}
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
                       onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">Plus anciens</a>
                    <a href="{{ route('player-matches.index', ['sort' => 'date_desc']) }}" 
                       style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_desc' ? 'background-color: #ffffff; color: #991b1b;' : 'background-color: rgba(255, 255, 255, 0.2); color: #fecaca;' }} text-decoration: none;"
                       onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                       onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">Plus récents</a>
                </div>
            </div>
        </div>

        @if($availableMatches->count() > 0)
            <!-- Table view (desktop) -->
            <div class="table-container bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Joueur</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Type</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Faction</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Localisation</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Disponibilité</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Format</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Statut</th>
                            @auth
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Demande</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                            @endauth
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($availableMatches as $match)
                            @php
                                $userRequest = auth()->check() ? $match->requests()->where('requester_id', auth()->id())->first() : null;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="text-xs font-medium text-gray-900">{{ $match->creator->name }}</span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $match->type === 'competitive' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $match->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    {{ $match->faction }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    {{ $match->getLocationDisplay() }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    {{ $match->getAvailabilityDisplay() }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    @if($match->army_points)
                                        {{ \App\Services\ArmyPointsService::getDeploymentLabel($match->army_points) }} ({{ $match->army_points }} pts)
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $match->is_setup_validated ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $match->getStatusLabel() }}
                                    </span>
                                </td>
                                @auth
                                    <td class="px-3 py-2 whitespace-nowrap text-xs">
                                        @if($userRequest)
                                            @if($userRequest->status === 'pending')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    En attente
                                                </span>
                                            @elseif($userRequest->status === 'accepted')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Acceptée
                                                </span>
                                            @elseif($userRequest->status === 'rejected')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Refusée
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs space-x-1">
                                        @if($match->is_setup_validated)
                                            <a href="{{ route('player-matches.summary', $match) }}" class="text-blue-600 hover:text-blue-700 font-medium">Voir</a>
                                            <a href="{{ route('player-match-requests.create', $match) }}" class="text-red-600 hover:text-red-700 font-medium">S'inscrire</a>
                                        @else
                                            <span class="text-gray-400 cursor-not-allowed">S'inscrire</span>
                                        @endif
                                    </td>
                                @endauth
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Cards view (mobile) -->
            <div class="cards-container">
                @foreach($availableMatches as $match)
                    @php
                        $userRequest = auth()->check() ? $match->requests()->where('requester_id', auth()->id())->first() : null;
                    @endphp
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $match->creator->name }}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $match->type === 'competitive' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800' }} mt-1">
                                    {{ $match->getTypeLabel() }}
                                </span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $match->is_setup_validated ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $match->getStatusLabel() }}
                            </span>
                        </div>
                        
                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Faction :</span>
                                <span class="font-medium text-gray-900">{{ $match->faction }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Localisation :</span>
                                <span class="font-medium text-gray-900">{{ $match->getLocationDisplay() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Disponibilité :</span>
                                <span class="font-medium text-gray-900">{{ $match->getAvailabilityDisplay() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Format :</span>
                                <span class="font-medium text-gray-900">
                                    @if($match->army_points)
                                        {{ \App\Services\ArmyPointsService::getDeploymentLabel($match->army_points) }} ({{ $match->army_points }} pts)
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </span>
                            </div>
                            @if($userRequest)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Demande :</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $userRequest->status === 'pending' ? 'bg-red-100 text-red-800' : ($userRequest->status === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($userRequest->status) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        @auth
                            <div class="flex gap-2">
                                @if($match->is_setup_validated)
                                    <a href="{{ route('player-matches.summary', $match) }}" class="flex-1 text-center bg-blue-600 text-white py-2 rounded text-sm font-medium hover:bg-blue-700 transition">Voir</a>
                                    <a href="{{ route('player-match-requests.create', $match) }}" class="flex-1 text-center bg-red-600 text-white py-2 rounded text-sm font-medium hover:bg-red-700 transition">S'inscrire</a>
                                @else
                                    <button disabled class="flex-1 text-center bg-gray-300 text-gray-500 py-2 rounded text-sm font-medium cursor-not-allowed">S'inscrire</button>
                                @endif
                            </div>
                        @endauth
                    </div>
                @endforeach
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

    <!-- Matchs en cours (accessibles en spectateur) -->
    <div class="mb-12">
        <div style="background: linear-gradient(to right, #7c3aed, #6d28d9); padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 2px solid #5b21b6; margin-bottom: 1.5rem;">
            <div class="flex justify-between items-center">
                <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700;">👁️ Matchs en cours</h2>
                <div class="flex gap-2 items-center">
                    <span style="color: #e9d5ff; font-size: 0.875rem; font-weight: 500;">Trier :</span>
                    <a href="{{ route('player-matches.index', ['sort' => 'date_asc']) }}" 
                       style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_asc' ? 'background-color: #ffffff; color: #6d28d9;' : 'background-color: rgba(255, 255, 255, 0.2); color: #e9d5ff;' }} text-decoration: none;"
                       onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                       onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">Plus anciens</a>
                    <a href="{{ route('player-matches.index', ['sort' => 'date_desc']) }}" 
                       style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_desc' ? 'background-color: #ffffff; color: #6d28d9;' : 'background-color: rgba(255, 255, 255, 0.2); color: #e9d5ff;' }} text-decoration: none;"
                       onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                       onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">Plus récents</a>
                </div>
            </div>
        </div>

        @if($ongoingMatches->count() > 0)
            <!-- Table view (desktop) -->
            <div class="table-container bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Créateur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Adversaire</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Localisation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($ongoingMatches as $match)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $match->creator->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $match->opponent->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $match->type === 'competitive' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $match->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $match->getLocationDisplay() }}
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <a href="{{ route('player-matches.show', $match) }}" class="text-blue-600 hover:text-blue-700 font-medium">Voir</a>
                                    <a href="{{ route('player-matches.spectate', $match) }}" class="text-purple-600 hover:text-purple-700 font-medium whitespace-nowrap">👁️ Spectateur</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Cards view (mobile) -->
            <div class="cards-container">
                @foreach($ongoingMatches as $match)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $match->creator->name }} vs {{ $match->opponent->name }}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $match->type === 'competitive' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800' }} mt-1">
                                    {{ $match->getTypeLabel() }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Localisation :</span>
                                <span class="font-medium text-gray-900">{{ $match->getLocationDisplay() }}</span>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 flex-wrap">
                            <a href="{{ route('player-matches.show', $match) }}" class="flex-1 text-center bg-blue-600 text-white py-2 rounded text-sm font-medium hover:bg-blue-700 transition">Voir</a>
                            <a href="{{ route('player-matches.spectate', $match) }}" class="flex-1 text-center bg-purple-600 text-white py-2 rounded text-sm font-medium hover:bg-purple-700 transition">👁️ Spectateur</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <p class="text-gray-600">Aucun match en cours pour le moment</p>
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
                           onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">Plus anciens</a>
                        <a href="{{ route('player-matches.index', ['sort' => 'date_desc']) }}" 
                           style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_desc' ? 'background-color: #ffffff; color: #991b1b;' : 'background-color: rgba(255, 255, 255, 0.2); color: #fecaca;' }} text-decoration: none;"
                           onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                           onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">Plus récents</a>
                    </div>
                </div>
            </div>
            <!-- Table view (desktop) -->
            <div class="table-container bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
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
                                        <a href="{{ route('player-match-requests.index', $match) }}" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200 transition cursor-pointer">
                                            {{ $pendingRequests }} en attente
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    @if($match->status === 'completed')
                                        <!-- Match terminé : afficher le lien "Voir" -->
                                        <a href="{{ route('player-matches.show', $match) }}" class="text-blue-600 hover:text-blue-700 font-medium">Voir</a>
                                    @elseif(auth()->id() === $match->creator_id)
                                        <!-- Créateur : peut configurer ou voir -->
                                        <a href="{{ $match->is_setup_validated ? route('player-matches.summary', $match) : route('player-matches.setup', $match) }}" class="text-red-600 hover:text-red-700 font-medium">
                                            @if($match->is_setup_validated)
                                                Voir
                                            @else
                                                Configurer
                                            @endif
                                        </a>
                                    @else
                                        <!-- Adversaire : peut seulement voir -->
                                        <a href="{{ route('player-matches.summary', $match) }}" class="text-red-600 hover:text-red-700 font-medium">
                                            Voir
                                        </a>
                                    @endif
                                    <!-- Bouton spectateur (visible si confirmé ou complété, mais PAS pour les joueurs) -->
                                    @if(in_array($match->status, ['confirmed', 'completed']) && auth()->id() !== $match->creator_id && auth()->id() !== $match->opponent_id)
                                        <a href="{{ route('player-matches.spectate', $match) }}" class="text-purple-600 hover:text-purple-700 font-medium whitespace-nowrap">👁️ Spectateur</a>
                                    @endif
                                    @if($match->status === 'open' && !$match->is_setup_validated && auth()->id() === $match->creator_id)
                                        <a href="{{ route('player-matches.edit', $match) }}" class="text-blue-600 hover:text-blue-700 font-medium">Modifier</a>
                                    @endif
                                    @if($match->status === 'confirmed' && auth()->id() === $match->creator_id)
                                        <a href="{{ route('player-matches.score', $match) }}" class="text-green-600 hover:text-green-700 font-medium">Score</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Cards view (mobile) -->
            <div class="cards-container">
                @foreach($myProposedMatches as $match)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $match->faction }}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $match->type === 'competitive' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800' }} mt-1">
                                    {{ $match->getTypeLabel() }}
                                </span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $match->status === 'open' ? 'bg-blue-100 text-blue-800' : ($match->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $match->getStatusLabel() }}
                            </span>
                        </div>
                        
                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Localisation :</span>
                                <span class="font-medium text-gray-900">{{ $match->getLocationDisplay() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Disponibilité :</span>
                                <span class="font-medium text-gray-900">{{ $match->getAvailabilityDisplay() }}</span>
                            </div>
                            @php
                                $pendingRequests = $match->requests->where('status', 'pending')->count();
                            @endphp
                            @if($pendingRequests > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Demandes :</span>
                                    <a href="{{ route('player-match-requests.index', $match) }}" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200">
                                        {{ $pendingRequests }} en attente
                                    </a>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex gap-2 flex-wrap">
                            @if($match->status === 'completed')
                                <a href="{{ route('player-matches.show', $match) }}" class="flex-1 text-center bg-blue-600 text-white py-2 rounded text-sm font-medium hover:bg-blue-700 transition">Voir</a>
                            @elseif(auth()->id() === $match->creator_id)
                                <a href="{{ $match->is_setup_validated ? route('player-matches.summary', $match) : route('player-matches.setup', $match) }}" class="flex-1 text-center bg-red-600 text-white py-2 rounded text-sm font-medium hover:bg-red-700 transition">
                                    @if($match->is_setup_validated)
                                        Voir
                                    @else
                                        Configurer
                                    @endif
                                </a>
                            @else
                                <a href="{{ route('player-matches.summary', $match) }}" class="flex-1 text-center bg-red-600 text-white py-2 rounded text-sm font-medium hover:bg-red-700 transition">Voir</a>
                            @endif
                            @if($match->status === 'open' && !$match->is_setup_validated && auth()->id() === $match->creator_id)
                                <a href="{{ route('player-matches.edit', $match) }}" class="flex-1 text-center bg-blue-600 text-white py-2 rounded text-sm font-medium hover:bg-blue-700 transition">Modifier</a>
                            @endif
                            @if($match->status === 'confirmed' && auth()->id() === $match->creator_id)
                                <a href="{{ route('player-matches.score', $match) }}" class="flex-1 text-center bg-green-600 text-white py-2 rounded text-sm font-medium hover:bg-green-700 transition">Score</a>
                            @endif
                        </div>
                    </div>
                @endforeach
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
                           onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">Plus anciens</a>
                        <a href="{{ route('player-matches.index', ['sort' => 'date_desc']) }}" 
                           style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_desc' ? 'background-color: #ffffff; color: #047857;' : 'background-color: rgba(255, 255, 255, 0.2); color: #d1fae5;' }} text-decoration: none;"
                           onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                           onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">Plus récents</a>
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
                                <p>{{ $match->getLocationDisplay() }}</p>
                                <p>{{ $match->getAvailabilityDisplay() }}</p>
                                <p>{{ $match->faction }}</p>
                                @if($match->status === 'completed')
                                    <p class="font-semibold text-gray-900">Score: {{ $match->creator_score }} - {{ $match->opponent_score }}</p>
                                @endif
                            </div>

                            <div class="space-y-2">
                                @if($match->status === 'completed')
                                    <!-- Match terminé : afficher le lien "Voir" -->
                                    <a href="{{ route('player-matches.show', $match) }}" class="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition text-center block">
                                        Voir
                                    </a>
                                @elseif(auth()->id() === $match->creator_id)
                                    <!-- Créateur : peut configurer -->
                                    @if($match->status !== 'completed')
                                        <div>
                                            <livewire:match-setup-modal :match="$match" />
                                        </div>
                                    @endif
                                    @if($match->is_setup_validated)
                                        <a href="{{ route('player-matches.summary', $match) }}" class="w-full bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition text-center block">
                                            Voir la configuration
                                        </a>
                                        <a href="{{ route('player-matches.show', $match) }}" class="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition text-center block">
                                            Résumé
                                        </a>
                                        <!-- Bouton spectateur (visible si confirmé ou complété, mais PAS pour les joueurs) -->
                                        @if(in_array($match->status, ['confirmed', 'completed']) && auth()->id() !== $match->creator_id && auth()->id() !== $match->opponent_id)
                                            <a href="{{ route('player-matches.spectate', $match) }}" class="w-full bg-purple-600 text-white py-2 rounded-lg font-semibold hover:bg-purple-700 transition text-center block">
                                                👁️ Spectateur
                                            </a>
                                        @endif
                                        @if($match->status === 'confirmed')
                                            <a href="{{ route('player-matches.score-creator', $match) }}" class="w-full bg-green-600 text-white py-2 rounded-lg font-semibold hover:bg-green-700 transition text-center block">
                                                Saisir le score
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('player-matches.setup', $match) }}" class="w-full bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition text-center block">
                                            Configurer
                                        </a>
                                    @endif
                                @else
                                    <!-- Adversaire : peut seulement voir -->
                                    <a href="{{ route('player-matches.summary', $match) }}" class="w-full bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition text-center block">
                                        Voir la configuration
                                    </a>
                                    <a href="{{ route('player-matches.show', $match) }}" class="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition text-center block">
                                        Résumé
                                    </a>
                                    <!-- Bouton spectateur (visible si confirmé ou complété, mais PAS pour les joueurs) -->
                                    @if(in_array($match->status, ['confirmed', 'completed']) && auth()->id() !== $match->creator_id && auth()->id() !== $match->opponent_id)
                                        <a href="{{ route('player-matches.spectate', $match) }}" class="w-full bg-purple-600 text-white py-2 rounded-lg font-semibold hover:bg-purple-700 transition text-center block">
                                            👁️ Spectateur
                                        </a>
                                    @endif
                                    @if($match->status === 'confirmed')
                                        <a href="{{ route('player-matches.score-opponent', $match) }}" class="w-full bg-green-600 text-white py-2 rounded-lg font-semibold hover:bg-green-700 transition text-center block">
                                            Saisir le score
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Historique des matchs terminés -->
        @auth
            @if($completedMatches->count() > 0)
                <div class="mb-12">
                    <div style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 2px solid #374151; margin-bottom: 1.5rem;">
                        <div class="flex justify-between items-center">
                            <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700;">Historique des matchs</h2>
                            <div class="flex gap-2 items-center">
                                <span style="color: #e5e7eb; font-size: 0.875rem; font-weight: 500;">Trier :</span>
                                <a href="{{ route('player-matches.index', ['sort' => 'date_asc']) }}" 
                                   style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_asc' ? 'background-color: #ffffff; color: #4b5563;' : 'background-color: rgba(255, 255, 255, 0.2); color: #e5e7eb;' }} text-decoration: none;"
                                   onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                                   onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_asc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">Plus anciens</a>
                                <a href="{{ route('player-matches.index', ['sort' => 'date_desc']) }}" 
                                   style="padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s; {{ $sortBy === 'date_desc' ? 'background-color: #ffffff; color: #4b5563;' : 'background-color: rgba(255, 255, 255, 0.2); color: #e5e7eb;' }} text-decoration: none;"
                                   onmouseover="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.3)' }}'"
                                   onmouseout="this.style.backgroundColor='{{ $sortBy === 'date_desc' ? '#ffffff' : 'rgba(255, 255, 255, 0.2)' }}'">Plus récents</a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Adversaire</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Faction</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Localisation</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Score</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Résultat</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($completedMatches as $match)
                                    @php
                                        $isCreator = auth()->id() === $match->creator_id;
                                        $myScore = $isCreator ? $match->creator_score : $match->opponent_score;
                                        $opponentScore = $isCreator ? $match->opponent_score : $match->creator_score;
                                        $opponentName = $isCreator ? $match->opponent->name : $match->creator->name;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $opponentName }}
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                            <span class="text-gray-900">{{ $myScore }}</span>
                                            <span class="text-gray-500 mx-1">-</span>
                                            <span class="text-gray-900">{{ $opponentScore }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($match->is_draw)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Nul
                                                </span>
                                            @elseif($match->winner_id === auth()->id())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Victoire
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Défaite
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $match->played_at ? $match->played_at->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                            <a href="{{ route('player-matches.show', $match) }}" class="text-blue-600 hover:text-blue-700 font-medium">Détails</a>
                                            @if(auth()->id() !== $match->creator_id && auth()->id() !== $match->opponent_id)
                                                <a href="{{ route('player-matches.spectate', $match) }}" class="text-purple-600 hover:text-purple-700 font-medium whitespace-nowrap">👁️ Spectateur</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endauth
        </div>
    </div>
</div>
@endsection
