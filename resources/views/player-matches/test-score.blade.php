@extends('layouts.public')

@php
use Illuminate\Support\Facades\DB;
@endphp

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 overflow-hidden shadow-md sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-white">
                            Test de Scoring
                        </h2>
                        <p class="text-red-100 mt-1">
                            Match entre {{ $playerMatch->creator->name }} et {{ $playerMatch->opponent->name }}
                        </p>
                    </div>
                    <a href="{{ route('player-matches.show', $playerMatch) }}" 
                       class="text-white hover:text-red-100 font-semibold">
                        ← Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
            <!-- Colonne gauche : Missions et Péripéties -->
            <div class="lg:col-span-2 space-y-4 lg:space-y-6 order-2 lg:order-1">
                <!-- Mission Primaire -->
                @if($playerMatch->primaryMission)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                        <button type="button" onclick="document.getElementById('primary-content').classList.toggle('hidden')" class="w-full flex justify-between items-center">
                            <h3 class="text-base md:text-lg font-semibold text-gray-900">Mission Primaire</h3>
                            <span class="text-gray-600 text-lg" id="primary-toggle">−</span>
                        </button>
                        
                        <div id="primary-content" class="mt-3 md:mt-4">
                            <!-- Titre -->
                            <div class="mb-3 md:mb-4">
                                @php
                                    $primaryFr = DB::table('translations')
                                        ->where('resource_type', 'PrimaryMission')
                                        ->where('resource_id', $playerMatch->primaryMission->id)
                                        ->where('field', 'name')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                    
                                    $primaryFullFr = DB::table('translations')
                                        ->where('resource_type', 'PrimaryMission')
                                        ->where('resource_id', $playerMatch->primaryMission->id)
                                        ->where('field', 'full_text')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                @endphp
                                <div class="space-y-2 md:flex md:gap-4 md:space-y-0">
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Anglais</p>
                                        <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $playerMatch->primaryMission->name }}</p>
                                    </div>
                                    @if($primaryFr)
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Français</p>
                                            <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $primaryFr }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Texte complet -->
                            <div class="border-t border-gray-200 pt-3 md:pt-4">
                                <div class="flex gap-2 mb-2">
                                    <button type="button" onclick="document.getElementById('primary-en').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">EN</button>
                                    @if($primaryFullFr)
                                        <button type="button" onclick="document.getElementById('primary-fr').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">FR</button>
                                    @endif
                                </div>
                                <div id="primary-en" class="bg-gray-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto">
                                    <p class="text-xs md:text-sm text-gray-700 whitespace-pre-wrap">{{ $playerMatch->primaryMission->full_text }}</p>
                                </div>
                                @if($primaryFullFr)
                                    <div id="primary-fr" class="hidden bg-gray-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto">
                                        <p class="text-xs md:text-sm text-gray-700 whitespace-pre-wrap">{{ $primaryFullFr }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Péripétie -->
                @if($playerMatch->twistMission)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                        <button type="button" onclick="document.getElementById('twist-content').classList.toggle('hidden')" class="w-full flex justify-between items-center">
                            <h3 class="text-base md:text-lg font-semibold text-gray-900">Péripétie</h3>
                            <span class="text-gray-600 text-lg" id="twist-toggle">−</span>
                        </button>
                        
                        <div id="twist-content" class="mt-3 md:mt-4">
                            <!-- Titre -->
                            <div class="mb-3 md:mb-4">
                                @php
                                    $twistFr = DB::table('translations')
                                        ->where('resource_type', 'TwistMission')
                                        ->where('resource_id', $playerMatch->twistMission->id)
                                        ->where('field', 'name')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                    
                                    $twistFullFr = DB::table('translations')
                                        ->where('resource_type', 'TwistMission')
                                        ->where('resource_id', $playerMatch->twistMission->id)
                                        ->where('field', 'full_text')
                                        ->where('locale', 'fr')
                                        ->value('translated_text');
                                @endphp
                                <div class="space-y-2 md:flex md:gap-4 md:space-y-0">
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Anglais</p>
                                        <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $playerMatch->twistMission->name }}</p>
                                    </div>
                                    @if($twistFr)
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Français</p>
                                            <p class="text-xs md:text-sm font-semibold text-gray-900 break-words">{{ $twistFr }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Texte complet -->
                            <div class="border-t border-gray-200 pt-3 md:pt-4">
                                <div class="flex gap-2 mb-2">
                                    <button type="button" onclick="document.getElementById('twist-en').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">EN</button>
                                    @if($twistFullFr)
                                        <button type="button" onclick="document.getElementById('twist-fr').classList.toggle('hidden')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">FR</button>
                                    @endif
                                </div>
                                <div id="twist-en" class="bg-gray-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto">
                                    <p class="text-xs md:text-sm text-gray-700 whitespace-pre-wrap">{{ $playerMatch->twistMission->full_text }}</p>
                                </div>
                                @if($twistFullFr)
                                    <div id="twist-fr" class="hidden bg-gray-50 p-3 md:p-4 rounded-lg max-h-64 md:max-h-96 overflow-y-auto">
                                        <p class="text-xs md:text-sm text-gray-700 whitespace-pre-wrap">{{ $twistFullFr }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Colonne droite : Formulaire de scoring -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 lg:h-fit order-1 lg:order-2">
                <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4">Formulaire de Test</h3>
                
                <form class="space-y-3 md:space-y-4">
                    <!-- Résultat du match -->
                    <div class="space-y-2">
                        <label class="block text-xs md:text-sm font-semibold text-gray-900">Résultat du match</label>
                        <div class="space-y-1.5 md:space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="result" value="nul" checked class="mr-2 w-4 h-4">
                                <span class="text-xs md:text-sm text-gray-700">Nul</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="result" value="creator_abandon" class="mr-2 w-4 h-4">
                                <span class="text-xs md:text-sm text-gray-700 truncate">{{ $playerMatch->creator->name }} abandonne</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="result" value="opponent_abandon" class="mr-2 w-4 h-4">
                                <span class="text-xs md:text-sm text-gray-700 truncate">{{ $playerMatch->opponent->name }} abandonne</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="result" value="creator_table_rase" class="mr-2 w-4 h-4">
                                <span class="text-xs md:text-sm text-gray-700 truncate">{{ $playerMatch->creator->name }} table rase</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="result" value="opponent_table_rase" class="mr-2 w-4 h-4">
                                <span class="text-xs md:text-sm text-gray-700 truncate">{{ $playerMatch->opponent->name }} table rase</span>
                            </label>
                        </div>
                    </div>

                    <hr class="my-2 md:my-4">

                    <!-- Points créateur -->
                    <div class="space-y-2">
                        <p class="text-xs md:text-sm font-semibold text-gray-900 truncate">{{ $playerMatch->creator->name }}</p>
                        <div>
                            <label class="text-xs font-semibold text-gray-600">Points primaires (max 50)</label>
                            <input type="number" min="0" max="50" value="0" class="w-full px-2 py-1 border border-gray-300 rounded text-xs md:text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600">Points secondaires (max 40)</label>
                            <input type="number" min="0" max="40" value="0" class="w-full px-2 py-1 border border-gray-300 rounded text-xs md:text-sm">
                        </div>
                        <label class="flex items-center text-xs md:text-sm">
                            <input type="checkbox" checked class="mr-2 w-4 h-4">
                            <span>Points peinture (+10)</span>
                        </label>
                    </div>

                    <hr class="my-2 md:my-4">

                    <!-- Points adversaire -->
                    <div class="space-y-2">
                        <p class="text-xs md:text-sm font-semibold text-gray-900 truncate">{{ $playerMatch->opponent->name }}</p>
                        <div>
                            <label class="text-xs font-semibold text-gray-600">Points primaires (max 50)</label>
                            <input type="number" min="0" max="50" value="0" class="w-full px-2 py-1 border border-gray-300 rounded text-xs md:text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600">Points secondaires (max 40)</label>
                            <input type="number" min="0" max="40" value="0" class="w-full px-2 py-1 border border-gray-300 rounded text-xs md:text-sm">
                        </div>
                        <label class="flex items-center text-xs md:text-sm">
                            <input type="checkbox" checked class="mr-2 w-4 h-4">
                            <span>Points peinture (+10)</span>
                        </label>
                    </div>

                    <button type="button" class="w-full bg-blue-600 text-white py-2 md:py-3 rounded-lg font-semibold hover:bg-blue-700 transition text-xs md:text-sm mt-3 md:mt-4">
                        Tester le formulaire
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
