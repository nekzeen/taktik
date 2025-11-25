@extends('layouts.public')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header -->
    <div class="bg-gradient-to-br from-red-600 via-red-700 to-red-800 border-b-4 border-red-900 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">📚 Forum des Règles</h1>
                    <p class="text-red-100">Posez vos questions et trouvez des réponses sur les règles du jeu</p>
                </div>
                @auth
                    <a href="{{ route('rule-discussions.create') }}" class="bg-white text-red-600 px-6 py-3 rounded-lg font-bold hover:bg-red-50 transition shadow-lg">
                        ➕ Nouvelle Question
                    </a>
                @else
                    <a href="{{ route('login') }}" class="bg-white text-red-600 px-6 py-3 rounded-lg font-bold hover:bg-red-50 transition shadow-lg">
                        🔐 Se Connecter
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" action="{{ route('rule-discussions.index') }}" class="space-y-4">
                <!-- Search Bar -->
                <div class="flex gap-2">
                    <div class="flex-1 relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $search }}" 
                            placeholder="🔍 Rechercher une question..." 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
                        >
                    </div>
                    <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                        Rechercher
                    </button>
                    @if($search || $selectedCategory || $selectedStatus)
                        <a href="{{ route('rule-discussions.index') }}" class="bg-gray-200 text-gray-900 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                            Réinitialiser
                        </a>
                    @endif
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Catégorie</label>
                        <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                            <option value="">-- Toutes les catégories --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $selectedCategory == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Statut</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                            <option value="">-- Tous les statuts --</option>
                            <option value="open" {{ $selectedStatus === 'open' ? 'selected' : '' }}>🟢 Ouvert</option>
                            <option value="closed" {{ $selectedStatus === 'closed' ? 'selected' : '' }}>🔴 Fermé</option>
                            <option value="resolved" {{ $selectedStatus === 'resolved' ? 'selected' : '' }}>✅ Résolu</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Trier par</label>
                        <select name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                            <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>📅 Plus récent</option>
                            <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>🔥 Plus populaire</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Info -->
        <div class="mb-6">
            <p class="text-gray-600 font-semibold">
                {{ $discussions->total() }} discussion{{ $discussions->total() > 1 ? 's' : '' }} trouvée{{ $discussions->total() > 1 ? 's' : '' }}
                @if($search)
                    pour "<strong>{{ $search }}</strong>"
                @endif
            </p>
        </div>

        <!-- Discussions List -->
        @forelse($discussions as $discussion)
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition mb-4 overflow-hidden border-l-4 border-red-600">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <!-- Main Content -->
                        <div class="flex-1">
                            <!-- Title -->
                            <a href="{{ route('rule-discussions.show', $discussion) }}" class="block">
                                <h3 class="text-xl font-bold text-gray-900 hover:text-red-600 transition mb-2">
                                    {{ $discussion->title }}
                                </h3>
                            </a>

                            <!-- Description Preview -->
                            <p class="text-gray-600 mb-3 line-clamp-2">
                                {{ Str::limit($discussion->description, 150) }}
                            </p>

                            <!-- Meta Info -->
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                <span class="flex items-center gap-1">
                                    👤 <strong>{{ $discussion->user->name }}</strong>
                                </span>
                                <span class="flex items-center gap-1">
                                    📁 
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">
                                        {{ $discussion->category->name }}
                                    </span>
                                </span>
                                <span class="flex items-center gap-1">
                                    📅 {{ $discussion->created_at->format('d M Y') }}
                                </span>
                                <span class="flex items-center gap-1">
                                    💬 {{ $discussion->approvedReplies->count() }} réponse{{ $discussion->approvedReplies->count() > 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="flex flex-col items-end gap-2">
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold
                                @if($discussion->status === 'open')
                                    bg-green-100 text-green-800
                                @elseif($discussion->status === 'closed')
                                    bg-red-100 text-red-800
                                @else
                                    bg-blue-100 text-blue-800
                                @endif
                            ">
                                @if($discussion->status === 'open')
                                    🟢 Ouvert
                                @elseif($discussion->status === 'closed')
                                    🔴 Fermé
                                @else
                                    ✅ Résolu
                                @endif
                            </span>
                            @if($discussion->is_pinned)
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                    📌 Épinglée
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-600 text-lg mb-4">
                    @if($search)
                        Aucune discussion trouvée pour "<strong>{{ $search }}</strong>"
                    @else
                        Aucune discussion pour le moment
                    @endif
                </p>
                @auth
                    <a href="{{ route('rule-discussions.create') }}" class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                        ➕ Créer la première question
                    </a>
                @endauth
            </div>
        @endforelse

        <!-- Pagination -->
        @if($discussions->hasPages())
            <div class="mt-8">
                {{ $discussions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
