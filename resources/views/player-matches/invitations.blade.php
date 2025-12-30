@extends('layouts.public')

@section('title', 'Invitations')

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-red-700 to-red-900 shadow-md sm:rounded-lg mb-6 p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    <a href="{{ route('player-matches.index') }}" class="text-white hover:text-red-100 text-xs font-medium mb-1 inline-block">
                        ← Retour aux matchs
                    </a>
                    <h1 class="text-2xl font-bold text-white">Invitations</h1>
                    <p class="mt-1 text-red-100 text-xs">Gérez les invitations pour votre match</p>
                </div>
                <a href="{{ route('player-matches.show', $playerMatch) }}" class="bg-white text-red-600 px-3 py-1.5 rounded font-medium hover:bg-red-50 transition text-xs whitespace-nowrap self-start">
                    Voir le match
                </a>
            </div>
        </div>

        <div class="max-w-7xl mx-auto">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Inviter un joueur</h2>

                        @if($playerMatch->opponent_id)
                            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                                Ce match a déjà un adversaire.
                            </div>
                        @else
                            <form action="{{ route('player-matches.invitations.store', $playerMatch) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="invited_user" class="block text-sm font-medium text-gray-700">Nom ou email</label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="invited_user"
                                            name="invited_user"
                                            value="{{ old('invited_user') }}"
                                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                            placeholder="Ex: Jean Dupont ou jean@exemple.com"
                                            autocomplete="off"
                                            required
                                        />
                                        <div id="invited_user_suggestions" class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden">
                                            <ul id="invited_user_suggestions_list" class="max-h-56 overflow-auto py-1"></ul>
                                        </div>
                                    </div>
                                    @error('invited_user')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-700">Message (optionnel)</label>
                                    <textarea
                                        id="message"
                                        name="message"
                                        rows="2"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                        placeholder="Ex: Salut ! Ça te dit une partie ?"
                                    >{{ old('message') }}</textarea>
                                    @error('message')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                                    Envoyer l'invitation (email)
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Invitations envoyées</h2>

                        @if($invitations->count() > 0)
                            <div class="space-y-3">
                                @foreach($invitations as $invitation)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-semibold text-gray-900">
                                                    {{ $invitation->invitedUser->name ?? 'Utilisateur supprimé' }}
                                                </p>
                                                <p class="text-xs text-gray-600">
                                                    {{ $invitation->invitedUser->email ?? '' }}
                                                </p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invitation->status === 'pending' ? 'bg-blue-100 text-blue-800' : ($invitation->status === 'accepted' ? 'bg-green-100 text-green-800' : ($invitation->status === 'declined' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ $invitation->status === 'pending' ? 'En attente' : ($invitation->status === 'accepted' ? 'Acceptée' : ($invitation->status === 'declined' ? 'Refusée' : 'Annulée')) }}
                                            </span>
                                        </div>

                                        @if($invitation->message)
                                            <p class="mt-3 text-sm text-gray-700 p-3 bg-gray-50 rounded border border-gray-200">
                                                {{ $invitation->message }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-600 text-sm">Aucune invitation envoyée pour le moment.</p>
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rappel</h3>
                        <div class="text-sm text-gray-700 space-y-2">
                            <p>
                                Les invitations sont disponibles uniquement tant que le match est <strong>ouvert</strong>.
                            </p>
                            <p>
                                Un email est envoyé automatiquement au joueur invité.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('invited_user');
    const box = document.getElementById('invited_user_suggestions');
    const list = document.getElementById('invited_user_suggestions_list');
    if (!input || !box || !list) return;

    let debounceTimer = null;
    let lastQuery = '';

    function hide() {
        box.classList.add('hidden');
        list.innerHTML = '';
    }

    function show() {
        box.classList.remove('hidden');
    }

    function render(items) {
        list.innerHTML = '';

        if (!items || items.length === 0) {
            hide();
            return;
        }

        for (const item of items) {
            const li = document.createElement('li');
            li.className = 'px-3 py-2 text-sm text-gray-900 hover:bg-red-50 cursor-pointer';
            li.textContent = item.label;
            li.addEventListener('mousedown', (e) => {
                e.preventDefault();
                input.value = item.value;
                hide();
            });
            list.appendChild(li);
        }

        show();
    }

    async function fetchSuggestions(q) {
        const res = await fetch(`/api/users/autocomplete?q=${encodeURIComponent(q)}`);
        if (!res.ok) return [];
        return await res.json();
    }

    input.addEventListener('input', () => {
        const q = (input.value || '').trim();
        if (q.length < 3) {
            lastQuery = q;
            hide();
            return;
        }

        lastQuery = q;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            const current = lastQuery;
            const items = await fetchSuggestions(current);
            if (current !== lastQuery) return;
            render(items);
        }, 200);
    });

    input.addEventListener('blur', () => {
        setTimeout(hide, 150);
    });

    document.addEventListener('click', (e) => {
        if (e.target === input || box.contains(e.target)) return;
        hide();
    });
});
</script>
