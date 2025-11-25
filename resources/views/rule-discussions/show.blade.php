@extends('layouts.public')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-br from-red-600 via-red-700 to-red-800 border-b-4 border-red-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <a href="{{ route('rule-discussions.index') }}" class="text-white hover:text-red-100 transition mb-4 inline-block">
                ← Retour aux discussions
            </a>
            <h1 class="text-3xl font-bold text-white">{{ $discussion->title }}</h1>
            <div class="flex items-center gap-4 mt-4 text-red-100 text-sm">
                <span>Par: <strong>{{ $discussion->user->name }}</strong></span>
                <span>Catégorie: <strong>{{ $discussion->category->name }}</strong></span>
                <span>Créé: <strong>{{ $discussion->created_at->format('d M Y à H:i') }}</strong></span>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <div class="flex items-start justify-between mb-6">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-4">
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

            <div class="prose prose-sm max-w-none mb-6">
                {{ $discussion->description }}
            </div>

            @if($discussion->image_path)
                <div class="mb-6">
                    <img src="{{ Storage::url($discussion->image_path) }}" alt="Image de la discussion" class="max-w-full h-auto rounded-lg">
                </div>
            @endif
        </div>

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">💬 Réponses ({{ $discussion->approvedReplies()->count() }})</h2>

            @forelse($discussion->approvedReplies as $reply)
                <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $reply->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $reply->created_at->format('d M Y à H:i') }}</p>
                        </div>
                    </div>

                    <div class="prose prose-sm max-w-none mb-4">
                        {{ $reply->content }}
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-md p-6 text-center text-gray-600">
                    Aucune réponse approuvée pour le moment
                </div>
            @endforelse
        </div>

        @auth
            @if(auth()->user()->hasRole(['player', 'moderator', 'admin', 'super-admin']))
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Votre réponse</h3>
                    <form method="POST" action="{{ route('rule-discussion-replies.store', $discussion) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contenu</label>
                            <textarea name="content" rows="6" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 @error('content') border-red-500 @enderror" placeholder="Votre réponse...">{{ old('content') }}</textarea>
                            @error('content')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                                ✅ Répondre
                            </button>
                            <a href="{{ route('rule-discussions.index') }}" class="bg-gray-200 text-gray-900 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            @endif
        @else
            <div class="bg-blue-50 border-l-4 border-blue-600 p-6 rounded">
                <p class="text-blue-800">
                    <a href="{{ route('login') }}" class="font-semibold hover:underline">Connectez-vous</a> pour répondre à cette discussion
                </p>
            </div>
        @endauth
    </div>
</div>
@endsection
