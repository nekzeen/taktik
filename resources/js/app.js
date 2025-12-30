import './bootstrap';

// Alpine est fourni par Livewire, ne pas l'importer ici
// Livewire gère automatiquement Alpine.start()

import Alpine from 'alpinejs';

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}
