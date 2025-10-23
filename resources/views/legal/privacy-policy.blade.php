@extends('layouts.public')

@section('title', 'Politique de Confidentialité')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Politique de Confidentialité</h1>

    <div class="prose prose-sm max-w-none text-gray-700 space-y-6">
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">1. Responsable de Traitement</h2>
            <p>Taktik<br>Contact : contact@gaelmorvan.fr</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">2. Données Collectées</h2>
            <ul class="list-disc pl-5 space-y-2">
                <li>Nom et prénom</li>
                <li>Adresse email</li>
                <li>Mot de passe (haché)</li>
                <li>Données de tournoi (faction, armée, localisation)</li>
                <li>Adresse IP et données de navigation (cookies)</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">3. Finalités du Traitement</h2>
            <ul class="list-disc pl-5 space-y-2">
                <li>Gestion des comptes utilisateurs</li>
                <li>Organisation et gestion des tournois</li>
                <li>Prévention des abus (reCAPTCHA)</li>
                <li>Amélioration du service</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">4. Durée de Conservation</h2>
            <p>Les données sont conservées pendant la durée du compte utilisateur. Après suppression du compte, les données sont supprimées dans un délai de 30 jours.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">5. Vos Droits</h2>
            <ul class="list-disc pl-5 space-y-2">
                <li><strong>Droit d'accès</strong> : Consulter vos données</li>
                <li><strong>Droit de rectification</strong> : Modifier vos données</li>
                <li><strong>Droit à l'oubli</strong> : Demander la suppression</li>
                <li><strong>Droit à la portabilité</strong> : Récupérer vos données</li>
            </ul>
            <p class="mt-3">Pour exercer ces droits, contactez : contact@gaelmorvan.fr</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">6. Sous-traitants</h2>
            <ul class="list-disc pl-5 space-y-2">
                <li><strong>Google reCAPTCHA</strong> : Prévention des abus</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">7. Sécurité</h2>
            <p>Vos données sont protégées par des mesures de sécurité appropriées incluant le chiffrement des mots de passe et l'utilisation de HTTPS.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mt-6 mb-3">8. Modifications</h2>
            <p>Cette politique peut être modifiée à tout moment. Les modifications seront notifiées sur cette page.</p>
        </section>
    </div>
</div>
@endsection
