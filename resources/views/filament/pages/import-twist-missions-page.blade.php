<x-filament-panels::page>
    {{ $this->form }}

    <div class="mt-6 flex gap-3">
        @foreach ($this->getFormActions() as $action)
            {{ $action }}
        @endforeach
    </div>
</x-filament-panels::page>
