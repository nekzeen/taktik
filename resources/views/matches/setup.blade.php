@extends('layouts.public')

@section('title', 'Configuration du match')

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md sm:rounded-lg mb-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Configuration du match</h1>
                    <p class="mt-2 text-red-100">
                        @if($matchType === 'tournament')
                            Table {{ $match->table_number ?? '#' . $match->id }} - Round {{ $match->round }}
                        @else
                            {{ auth()->id() === $match->creator_id ? $match->opponent?->name : $match->creator->name }}
                        @endif
                    </p>
                </div>
                <a href="{{ $matchType === 'tournament' ? route('tournaments.matches.index', $tournament) : route('player-matches.index') }}" 
                   class="px-4 py-2 bg-white text-red-600 rounded-lg font-medium hover:bg-red-50 transition">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Colonne gauche : Informations du match -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations du match</h2>
                    
                    <div class="space-y-4">
                        @if($matchType === 'tournament')
                            <!-- Joueur 1 -->
                            <div class="pb-4 border-b border-gray-200">
                                <p class="text-xs font-medium text-gray-500 uppercase">Joueur 1</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">{{ $match->player1->name }}</p>
                                @if($match->player1ArmyList?->faction)
                                    <p class="text-xs text-gray-600 mt-1">{{ $match->player1ArmyList->faction->name }}</p>
                                @endif
                            </div>

                            <!-- Joueur 2 -->
                            <div class="pb-4 border-b border-gray-200">
                                <p class="text-xs font-medium text-gray-500 uppercase">Joueur 2</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">{{ $match->player2->name }}</p>
                                @if($match->player2ArmyList?->faction)
                                    <p class="text-xs text-gray-600 mt-1">{{ $match->player2ArmyList->faction->name }}</p>
                                @endif
                            </div>
                        @else
                            <!-- Créateur -->
                            <div class="pb-4 border-b border-gray-200">
                                <p class="text-xs font-medium text-gray-500 uppercase">Créateur</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">{{ $match->creator->name }}</p>
                                <p class="text-xs text-gray-600 mt-1">{{ $match->faction }}</p>
                            </div>

                            <!-- Adversaire -->
                            @if($match->opponent)
                                <div class="pb-4 border-b border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase">Adversaire</p>
                                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $match->opponent->name }}</p>
                                </div>
                            @else
                                <div class="pb-4 border-b border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase">Adversaire</p>
                                    <p class="text-sm text-gray-600 mt-1">En attente</p>
                                </div>
                            @endif
                        @endif

                        <!-- Configuration actuelle -->
                        @if($match->is_setup_complete)
                            <div class="pb-4 border-b border-gray-200">
                                <p class="text-xs font-medium text-gray-500 uppercase mb-3">Configuration actuelle</p>
                                
                                @if($match->primaryMission)
                                    <div class="mb-2">
                                        <p class="text-xs text-gray-600">Mission primaire :</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ $match->primaryMission->name }}</p>
                                    </div>
                                @endif

                                @if($match->terrainLayout)
                                    <div class="mb-2">
                                        <p class="text-xs text-gray-600">Disposition de terrain :</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ $match->terrainLayout->name }}</p>
                                    </div>
                                @endif

                                @if($matchType === 'tournament' && $match->getDeploymentMode())
                                    <div class="mb-2">
                                        <p class="text-xs text-gray-600">Zone de déploiement :</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ $match->getDeploymentMode() }}</p>
                                    </div>
                                @elseif($matchType !== 'tournament' && $match->deployment_mode)
                                    <div class="mb-2">
                                        <p class="text-xs text-gray-600">Zone de déploiement :</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ $match->deployment_mode }}</p>
                                    </div>
                                @endif

                                @if($match->twistMission)
                                    <div>
                                        <p class="text-xs text-gray-600">Péripétie :</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ $match->twistMission->name }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- État du tirage -->
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-2">État</p>
                            @if($match->is_setup_complete)
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Configuré
                                </div>
                                <p class="text-xs text-gray-600 mt-2">Mode : <strong>{{ $match->setup_mode === 'random' ? 'Aléatoire' : 'Manuel' }}</strong></p>
                            @else
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Non configuré
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Configuration -->
            <div class="lg:col-span-2">
                <!-- Tirage aléatoire -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Tirage aléatoire</h2>
                    <p class="text-sm text-gray-600 mb-4">Générez automatiquement les éléments du match en un clic.</p>
                    
                    @if($matchType === 'tournament')
                        <!-- Choix du mode de tirage (tournoi uniquement) -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <!-- Mode normal -->
                            <button type="button" onclick="document.getElementById('form-randomize-normal').submit()" class="w-full px-3 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 active:bg-blue-800 transition flex flex-col items-center justify-center gap-1 border-0">
                                <span class="text-lg"></span>
                                <span class="text-xs font-semibold">Mode Normal</span>
                                <span class="text-xs font-normal opacity-80">Pool</span>
                            </button>
                            <form id="form-randomize-normal" action="{{ route('tournaments.matches.randomize', [$tournament, $match]) }}" method="POST" class="hidden">
                                @csrf
                                <input type="hidden" name="randomize_mode" value="normal">
                            </form>

                            <!-- Mode asymétrique -->
                            @if($options['asymmetric_primary_missions']->count() > 0)
                                <button type="button" onclick="document.getElementById('form-randomize-asymmetric').submit()" class="w-full px-3 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 active:bg-purple-800 transition flex flex-col items-center justify-center gap-1 border-0">
                                    <span class="text-lg"></span>
                                    <span class="text-xs font-semibold">Asymétrique</span>
                                    <span class="text-xs font-normal opacity-80">Aléatoire</span>
                                </button>
                                <form id="form-randomize-asymmetric" action="{{ route('tournaments.matches.randomize', [$tournament, $match]) }}" method="POST" class="hidden">
                                    @csrf
                                    <input type="hidden" name="randomize_mode" value="asymmetric">
                                </form>
                            @endif
                        </div>
                    @else
                        <!-- Mode simple pour les matchs non-tournoi -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <!-- Mode normal -->
                            <form action="{{ route('player-matches.randomize', $match) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                                    Normal
                                </button>
                            </form>

                            <!-- Mode asymétrique -->
                            @if($options['asymmetric_primary_missions']->count() > 0)
                                <form action="{{ route('player-matches.randomize', $match) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="randomize_mode" value="asymmetric">
                                    <button type="submit" class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition">
                                        Asymétrique
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Configuration manuelle -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Configuration manuelle</h2>
                    <p class="text-sm text-gray-600 mb-6">Sélectionnez manuellement les éléments du match.</p>

                    <form action="{{ $matchType === 'tournament' ? route('tournaments.matches.setup.update', [$tournament, $match]) : route('player-matches.setup.update', $match) }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Points d'armée -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Points d'armée
                            </label>
                            <select name="army_points" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                <option value="">-- Sélectionner --</option>
                                @foreach($armyPointsOptions as $value => $label)
                                    <option value="{{ $value }}" {{ ($match->army_points ?? 2000) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @if($match->army_points)
                                <p class="text-xs text-gray-500 mt-2">Actuellement : <strong>{{ $match->army_points }} points</strong></p>
                            @else
                                <p class="text-xs text-gray-500 mt-2">Par défaut : <strong>2000 points (Force de frappe)</strong></p>
                            @endif
                        </div>

                        <!-- Mission asymétrique (affichée si en mode asymétrique) - EN PREMIER -->
                        @if($match->asymmetric_primary_mission_id && $options['asymmetric_primary_missions']->count() > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Mission primaire asymétrique <span class="text-red-600">*</span>
                                </label>
                                <select name="asymmetric_primary_mission_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($options['asymmetric_primary_missions'] as $mission)
                                        <option value="{{ $mission->id }}" {{ $match->asymmetric_primary_mission_id === $mission->id ? 'selected' : '' }}>
                                            {{ $mission->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($match->asymmetricPrimaryMission)
                                    <p class="text-xs text-gray-500 mt-2">Actuellement : <strong>{{ $match->asymmetricPrimaryMission->name }}</strong></p>
                                @endif
                            </div>
                        @endif

                        <!-- Mission primaire (masquée si mission asymétrique définie) -->
                        @if(!$match->asymmetric_primary_mission_id)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Mission primaire <span class="text-red-600">*</span>
                                </label>
                                <select name="primary_mission_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($options['primary_missions'] as $mission)
                                        <option value="{{ $mission->id }}" {{ $match->primary_mission_id === $mission->id ? 'selected' : '' }}>
                                            {{ $mission->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($match->primaryMission)
                                    <p class="text-xs text-gray-500 mt-2">Actuellement : <strong>{{ $match->primaryMission->name }}</strong></p>
                                @endif
                            </div>
                        @endif

                        <!-- Disposition de terrain -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Disposition de terrain <span class="text-red-600">*</span>
                            </label>
                            <select name="terrain_layout_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                <option value="">-- Sélectionner --</option>
                                @foreach($options['terrain_layouts'] as $terrain)
                                    <option value="{{ $terrain->id }}" {{ $match->terrain_layout_id === $terrain->id ? 'selected' : '' }}>
                                        {{ $terrain->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($match->terrainLayout)
                                <p class="text-xs text-gray-500 mt-2">Actuellement : <strong>{{ $match->terrainLayout->name }}</strong></p>
                            @endif
                        </div>

                        <!-- Zone de déploiement (pour les deux types) -->
                        @if(isset($deploymentModes))
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Zone de déploiement
                                </label>
                                <select name="deployment_mode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($deploymentModes as $mode)
                                        <option value="{{ $mode }}" {{ $match->deployment_mode === $mode ? 'selected' : '' }}>
                                            {{ $mode }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($match->deployment_mode)
                                    <p class="text-xs text-gray-500 mt-2">Actuellement : <strong>{{ $match->deployment_mode }}</strong></p>
                                @endif
                            </div>
                        @endif

                        <!-- Péripétie -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Péripétie <span class="text-red-600">*</span>
                            </label>
                            <select name="twist_mission_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                <option value="">-- Sélectionner --</option>
                                @foreach($options['twist_missions'] as $twist)
                                    <option value="{{ $twist->id }}" {{ $match->twist_mission_id === $twist->id ? 'selected' : '' }}>
                                        {{ $twist->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($match->twistMission)
                                <p class="text-xs text-gray-500 mt-2">Actuellement : <strong>{{ $match->twistMission->name }}</strong></p>
                            @endif
                        </div>

                        <!-- Boutons d'action -->
                        <div class="flex gap-3 pt-4 border-t border-gray-200">
                            <button type="submit" class="flex-1 px-4 py-3 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700 transition">
                                Visualiser
                            </button>
                            @if($match->is_setup_complete)
                                <form action="{{ $matchType === 'tournament' ? route('tournaments.matches.reset', [$tournament, $match]) : route('player-matches.reset', $match) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-3 bg-gray-500 text-white rounded-lg font-semibold hover:bg-gray-600 transition">
                                        Réinitialiser
                                    </button>
                                </form>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
