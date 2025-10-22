<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- En-tête -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                                <div style="background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); padding: 0.75rem; border-radius: 0.75rem; display: inline-flex;">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div>Agenda des disponibilités</div>
                                    <div class="text-sm font-normal text-gray-600 mt-1">
                                        {{ $tournament->name }} • <span class="font-semibold text-red-600">{{ $allAvailabilities->count() }}</span> disponibilité(s)
                                    </div>
                                </div>
                            </h2>
                        </div>
                        <a href="{{ route('tournaments.matches.index', $tournament) }}" 
                           class="text-red-600 hover:text-red-800 font-semibold flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Retour aux matchs
                        </a>
                    </div>
                </div>
            </div>

            @if($allAvailabilities->isEmpty())
                <!-- Message si aucune disponibilité -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune disponibilité définie</h3>
                        <p class="text-gray-600 mb-6">Les joueurs n'ont pas encore défini leurs disponibilités pour ce tournoi.</p>
                        <a href="{{ route('tournaments.matches.index', $tournament) }}" 
                           class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition">
                            Retour aux matchs
                        </a>
                    </div>
                </div>
            @else
                <!-- Disponibilités ponctuelles -->
                @if($singleAvailabilities->count() > 0)
                    <div class="mb-6">
                        <div style="background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Disponibilités ponctuelles</span>
                                <span style="background-color: rgba(255, 255, 255, 0.2); color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600;">
                                    {{ $singleAvailabilities->count() }}
                                </span>
                            </h3>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach($singleAvailabilities as $availability)
                                <div style="background-color: white; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; transition: all 0.2s;"
                                     onmouseover="this.style.borderColor='#b91c1c'; this.style.boxShadow='0 4px 8px rgba(185, 28, 28, 0.15)'"
                                     onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 1px 3px rgba(0, 0, 0, 0.1)'">
                                    <!-- En-tête avec nom du joueur -->
                                    <div style="background: linear-gradient(to right, #fee2e2, #fecaca); padding: 0.75rem; border-bottom: 1px solid #fca5a5;">
                                        <div class="flex items-center gap-2">
                                            <div style="width: 2rem; height: 2rem; background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); border-radius: 9999px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem;">
                                                {{ strtoupper(substr($availability->user->name, 0, 1)) }}
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="font-weight: 700; color: #111827; font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $availability->user->name }}</h4>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Détails de la disponibilité -->
                                    <div style="padding: 0.75rem;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; padding: 0.5rem; background-color: #fef3c7; border-radius: 0.375rem; border: 1px solid #fde047;">
                                            <svg style="width: 1.25rem; height: 1.25rem; color: #ca8a04; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span style="font-weight: 700; color: #92400e; font-size: 0.875rem;">
                                                {{ $availability->available_at->format('d/m/Y à H:i') }}
                                            </span>
                                        </div>

                                        @if($availability->notes)
                                            <div style="margin-top: 0.75rem; padding: 0.5rem; background-color: #f9fafb; border-radius: 0.375rem; border: 1px solid #e5e7eb;">
                                                <p style="font-size: 0.75rem; color: #4b5563;">
                                                    <span style="font-weight: 600;">💬</span> {{ $availability->notes }}
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Temps restant -->
                                        <div style="margin-top: 0.75rem; font-size: 0.75rem; color: #6b7280; display: flex; align-items: center; gap: 0.25rem;">
                                            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Dans {{ $availability->available_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Disponibilités sur période -->
                @if($periodAvailabilities->count() > 0)
                    <div class="mb-6">
                        <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Disponibilités sur période</span>
                                <span style="background-color: rgba(255, 255, 255, 0.2); color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600;">
                                    {{ $periodAvailabilities->count() }}
                                </span>
                            </h3>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach($periodAvailabilities as $availability)
                                <div style="background-color: white; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; transition: all 0.2s;"
                                     onmouseover="this.style.borderColor='#059669'; this.style.boxShadow='0 4px 8px rgba(5, 150, 105, 0.15)'"
                                     onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 1px 3px rgba(0, 0, 0, 0.1)'">
                                    <!-- En-tête avec nom du joueur -->
                                    <div style="background: linear-gradient(to right, #d1fae5, #a7f3d0); padding: 0.75rem; border-bottom: 1px solid #6ee7b7;">
                                        <div class="flex items-center gap-2">
                                            <div style="width: 2rem; height: 2rem; background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 9999px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem;">
                                                {{ strtoupper(substr($availability->user->name, 0, 1)) }}
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="font-weight: 700; color: #111827; font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $availability->user->name }}</h4>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Détails de la disponibilité -->
                                    <div style="padding: 0.75rem;">
                                        <div style="margin-bottom: 0.75rem;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; padding: 0.5rem; background-color: #ecfdf5; border-radius: 0.375rem; border: 1px solid #6ee7b7;">
                                                <svg style="width: 1rem; height: 1rem; color: #059669; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                                </svg>
                                                <span style="font-size: 0.75rem; color: #047857; font-weight: 700;">
                                                    {{ $availability->available_from->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; background-color: #ecfdf5; border-radius: 0.375rem; border: 1px solid #6ee7b7;">
                                                <svg style="width: 1rem; height: 1rem; color: #059669; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                </svg>
                                                <span style="font-size: 0.75rem; color: #047857; font-weight: 700;">
                                                    {{ $availability->available_to->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Durée -->
                                        <div style="margin-bottom: 0.75rem; padding: 0.5rem; background-color: #d1fae5; border-radius: 0.375rem; border: 1px solid #6ee7b7; text-align: center;">
                                            <span style="font-size: 0.75rem; font-weight: 700; color: #065f46;">
                                                ⏱️ {{ $availability->available_from->diffInDays($availability->available_to) }} jour(s)
                                            </span>
                                        </div>

                                        @if($availability->notes)
                                            <div style="margin-top: 0.75rem; padding: 0.5rem; background-color: #f9fafb; border-radius: 0.375rem; border: 1px solid #e5e7eb;">
                                                <p style="font-size: 0.75rem; color: #4b5563;">
                                                    <span style="font-weight: 600;">💬</span> {{ $availability->notes }}
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Statut -->
                                        @if($availability->available_from <= now() && $availability->available_to >= now())
                                            <div style="margin-top: 0.75rem; font-size: 0.75rem; font-weight: 700; color: #059669; display: flex; align-items: center; gap: 0.25rem;">
                                                <svg style="width: 1rem; height: 1rem;" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Disponible actuellement
                                            </div>
                                        @else
                                            <div style="margin-top: 0.75rem; font-size: 0.75rem; color: #6b7280; display: flex; align-items: center; gap: 0.25rem;">
                                                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Commence {{ $availability->available_from->diffForHumans() }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
