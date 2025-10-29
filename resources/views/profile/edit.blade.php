@extends('layouts.public')

@section('content')
<div class="py-12 bg-gray-300">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 overflow-hidden shadow-md sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-white">
                            Mon profil
                        </h2>
                        <p class="text-red-100 mt-1">
                            Gérez vos informations personnelles
                        </p>
                    </div>
                    <a href="{{ route('dashboard') }}" 
                       class="text-white hover:text-red-100 font-semibold">
                        ← Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Contenu -->
        <div class="space-y-6">
            <!-- Informations du profil -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- Mot de passe -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                @include('profile.partials.update-password-form')
            </div>

            <!-- Supprimer le compte -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
