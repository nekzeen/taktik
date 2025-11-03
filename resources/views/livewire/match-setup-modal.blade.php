<div>
    @if(!$isSetupValidated)
        <!-- Bouton pour aller à la page de configuration (si non validé) -->
        <a 
            href="{{ $setupUrl }}"
            class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm"
            title="Configurer le match"
        >
            Configurer
        </a>
    @endif
</div>
