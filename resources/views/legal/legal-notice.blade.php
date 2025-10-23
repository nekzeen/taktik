@extends('layouts.public')

@section('title', 'Mentions Légales')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Mentions Légales</h1>

    <div class="prose prose-sm max-w-none text-gray-700 space-y-6">
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">1. Éditeur du Site</h2>
            <p>
                <strong>Taktik</strong><br>
                Responsable : Gaël Morvan<br>
                Email : contact@gaelmorvan.fr
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">2. Hébergement</h2>
            <p>Ce site est hébergé sur une infrastructure sécurisée avec certificat SSL/TLS.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">3. Propriété Intellectuelle</h2>
            <p>Le contenu du site (textes, images, logos) est protégé par les droits d'auteur. Toute reproduction sans autorisation est interdite.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">4. Limitation de Responsabilité</h2>
            <p>Taktik ne peut être tenu responsable des dommages directs ou indirects résultant de l'utilisation du site.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">5. Cookies</h2>
            <p>Le site utilise des cookies essentiels pour son fonctionnement. Vous pouvez gérer vos préférences de cookies via le banneau de consentement.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">6. Contact</h2>
            <p>Pour toute question ou réclamation : contact@gaelmorvan.fr</p>
        </section>
    </div>
</div>
@endsection
