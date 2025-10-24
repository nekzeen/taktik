@extends('layouts.public')

@section('title', 'Gestion des inscriptions - ' . $tournament->name)

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md sm:rounded-lg mb-6 p-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <a href="{{ route('tournaments.show', $tournament) }}" class="text-white hover:text-red-100 text-xs font-medium mb-1 inline-block">
                            ← Retour au tournoi
                        </a>
                        <h1 class="text-2xl font-bold text-white">Gestion des inscriptions</h1>
                        <p class="mt-1 text-red-100 text-xs">{{ $tournament->name }}</p>
                    </div>
                </div>
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

    <!-- Demandes en attente -->
    <div class="mb-12">
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md rounded mb-4 p-3">
            <h2 class="text-white font-semibold text-sm">
                Demandes en attente ({{ $pendingArmyLists->count() }})
            </h2>
        </div>

        @if($pendingArmyLists->count() > 0)
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($pendingArmyLists as $armyList)
                    <div class="bg-white rounded-lg shadow-sm border-2 border-yellow-200 overflow-hidden">
                        <!-- En-tête -->
                        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 1rem; border-bottom: 1px solid #fcd34d;">
                            <h3 class="text-lg font-bold text-gray-900">{{ $armyList->user->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $armyList->faction->name ?? 'Faction inconnue' }}</p>
                        </div>

                        <!-- Corps -->
                        <div class="p-4">
                            <div class="space-y-3 mb-4">
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Détachement</label>
                                    <p class="text-sm text-gray-900">{{ $armyList->detachment }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Points</label>
                                    <p class="text-sm text-gray-900">{{ $armyList->points }} pts</p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Taille PDF</label>
                                    <p class="text-sm text-gray-900">{{ number_format($armyList->pdf_size / 1024, 2) }} KB</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 mb-3">
                                <a href="{{ route('tournaments.army-list.pdf', [$tournament, $armyList]) }}" 
                                   target="_blank"
                                   class="flex-1 bg-red-600 text-white px-3 py-2 rounded-lg font-medium hover:bg-red-700 transition text-sm text-center">
                                    Voir PDF
                                </a>
                            </div>

                            <!-- Formulaire de rejet -->
                            <form method="POST" action="{{ route('tournaments.army-list.reject', [$tournament, $armyList]) }}" class="mb-3">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="rejection_reason" 
                                              placeholder="Raison du rejet (optionnel)" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-red-500"
                                              rows="2"></textarea>
                                </div>
                                <button type="submit" class="w-full bg-gray-400 text-white px-3 py-2 rounded-lg font-medium hover:bg-gray-500 transition text-sm">
                                    Rejeter
                                </button>
                            </form>

                            <!-- Bouton de validation -->
                            <form method="POST" action="{{ route('tournaments.army-list.validate', [$tournament, $armyList]) }}">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 text-white px-3 py-2 rounded-lg font-medium hover:bg-red-700 transition text-sm">
                                    Valider
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <p class="text-gray-600">Aucune demande en attente</p>
            </div>
        @endif
    </div>

    <!-- Listes validées -->
    @if($validatedArmyLists->count() > 0)
        <div class="mb-12">
            <div class="bg-gradient-to-r from-green-700 to-green-800 shadow-md rounded mb-4 p-3">
                <h2 class="text-white font-semibold text-sm">
                    Listes validées ({{ $validatedArmyLists->count() }})
                </h2>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Joueur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Faction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Détachement</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Points</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($validatedArmyLists as $armyList)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $armyList->user->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $armyList->faction->name ?? 'Inconnue' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $armyList->detachment }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $armyList->points }} pts
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('tournaments.army-list.pdf', [$tournament, $armyList]) }}" 
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-700 font-medium">
                                        Voir PDF
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Listes rejetées -->
    @if($rejectedArmyLists->count() > 0)
        <div class="mb-12">
            <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md rounded mb-4 p-3">
                <h2 class="text-white font-semibold text-sm">
                    Listes rejetées ({{ $rejectedArmyLists->count() }})
                </h2>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Joueur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Faction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Raison du rejet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($rejectedArmyLists as $armyList)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $armyList->user->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $armyList->faction->name ?? 'Inconnue' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $armyList->rejection_reason ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('tournaments.army-list.pdf', [$tournament, $armyList]) }}" 
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-700 font-medium">
                                        Voir PDF
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        </div>
    </div>
</div>
@endsection
