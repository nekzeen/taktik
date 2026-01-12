<!-- Modal pour définir la disponibilité globale -->
<div id="player-availability-modal" class="hidden fixed inset-0 bg-black bg-opacity-70 overflow-y-auto h-full w-full z-50 backdrop-blur-sm">
    <div class="relative top-20 mx-auto p-6 w-full max-w-md shadow-2xl rounded-xl" style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); border: 2px solid #374151;">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span style="font-size: 1.5rem;">📅</span>
                <span>Ma disponibilité</span>
            </h3>
            <button onclick="closePlayerAvailabilityModal()" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="mb-4 p-3 rounded-lg" style="background-color: rgba(59, 130, 246, 0.1); border: 1px solid #3b82f6;">
            <p class="text-sm" style="color: #93c5fd;">
                Cette disponibilité s'appliquera à <strong>tous vos matchs</strong> de ce tournoi.
            </p>
        </div>

        <form action="{{ route('tournaments.player-availability.store', $tournament) }}" method="POST">
            @csrf
            
            <!-- Type de disponibilité -->
            <div class="mb-5">
                <label class="block text-sm font-semibold mb-3" style="color: #e5e7eb;">Type de disponibilité</label>
                <div class="space-y-3">
                    <label class="flex items-center p-3 rounded-lg cursor-pointer transition-all" style="background-color: rgba(55, 65, 81, 0.5); border: 2px solid #374151;" onmouseover="this.style.borderColor='#b91c1c'; this.style.backgroundColor='rgba(185, 28, 28, 0.1)'" onmouseout="this.style.borderColor='#374151'; this.style.backgroundColor='rgba(55, 65, 81, 0.5)'">
                        <input type="radio" name="type" value="single" checked 
                               onclick="togglePlayerAvailabilityType('single')"
                               class="text-red-600 focus:ring-red-500 w-4 h-4">
                        <span class="ml-3 text-sm font-medium" style="color: #f3f4f6;">📍 Ponctuelle (date et heure précise)</span>
                    </label>
                </div>
            </div>

            <!-- Disponibilité ponctuelle -->
            <div id="player-single-fields" class="mb-5">
                <label for="player_available_at" class="block text-sm font-semibold mb-2" style="color: #e5e7eb;">
                    🕒 Date et heure
                </label>
                <input type="datetime-local" 
                       id="player_available_at" 
                       name="available_at"
                       min="{{ now()->format('Y-m-d\TH:i') }}"
                       class="w-full rounded-lg shadow-sm font-medium" style="background-color: #374151; border: 2px solid #4b5563; padding: 0.625rem; color: #f9fafb;" onfocus="this.style.borderColor='#b91c1c'; this.style.backgroundColor='#1f2937'" onblur="this.style.borderColor='#4b5563'; this.style.backgroundColor='#374151'">
            </div>

            <!-- Notes -->
            <div class="mb-6">
                <label for="player_notes" class="block text-sm font-semibold mb-2" style="color: #e5e7eb;">
                    💬 Note (optionnel)
                </label>
                <textarea id="player_notes" 
                          name="notes" 
                          rows="3" 
                          maxlength="500"
                          placeholder="Ex: Je préfère jouer en soirée..."
                          class="w-full rounded-lg shadow-sm font-medium placeholder-gray-400" style="background-color: #374151; border: 2px solid #4b5563; padding: 0.625rem; resize: none; color: #f9fafb;" onfocus="this.style.borderColor='#b91c1c'; this.style.backgroundColor='#1f2937'" onblur="this.style.borderColor='#4b5563'; this.style.backgroundColor='#374151'"></textarea>
                <div class="text-xs mt-1 text-right" style="color: #9ca3af;" id="player-char-count">0 / 500</div>
            </div>

            <!-- Boutons -->
            <div class="flex gap-3">
                <button type="button" 
                        onclick="closePlayerAvailabilityModal()"
                        class="flex-1 px-4 py-3 rounded-lg font-semibold transition-all shadow-md" style="background-color: #374151; color: #d1d5db; border: 2px solid #4b5563;" onmouseover="this.style.backgroundColor='#4b5563'; this.style.borderColor='#6b7280'" onmouseout="this.style.backgroundColor='#374151'; this.style.borderColor='#4b5563'">
                    Annuler
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-3 rounded-lg font-semibold transition-all shadow-lg" style="background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); color: white; border: 2px solid #7f1d1d;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 16px rgba(185, 28, 28, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'">
                    ✔️ Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPlayerAvailabilityModal() {
    document.getElementById('player-availability-modal').classList.remove('hidden');
    // Initialiser le compteur de caractères
    const textarea = document.getElementById('player_notes');
    const counter = document.getElementById('player-char-count');
    if (textarea && counter) {
        textarea.addEventListener('input', function() {
            counter.textContent = this.value.length + ' / 500';
        });
    }
}

function closePlayerAvailabilityModal() {
    document.getElementById('player-availability-modal').classList.add('hidden');
}

function togglePlayerAvailabilityType(type) {
    const singleFields = document.getElementById('player-single-fields');
    
    if (type === 'single') {
        singleFields.classList.remove('hidden');
    }
}
</script>
