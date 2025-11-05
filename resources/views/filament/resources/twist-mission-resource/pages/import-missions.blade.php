<x-filament-panels::page>
    <form wire:submit="import" class="space-y-6">
        {{ $this->form }}

        <div class="flex gap-3 justify-end">
            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Resources\TwistMissionResource::getUrl('index') }}"
                color="gray">
                Annuler
            </x-filament::button>
            <x-filament::button type="submit">
                Importer les péripéties
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
