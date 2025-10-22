import './bootstrap';

import Alpine from 'alpinejs';

// Ne démarrer Alpine que s'il n'est pas déjà démarré par Livewire
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}
