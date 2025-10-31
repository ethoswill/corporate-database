<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Manage Your Locations
        </x-slot>
        <x-slot name="description">
            Add and manage the locations for your company. Each location is specific to your account.
        </x-slot>

        @php
            $locationResource = \App\Filament\Resources\LocationResource::class;
            $listLocationPage = $locationResource::getUrl('index');
        @endphp

        <div class="mt-4">
            <iframe 
                src="{{ $listLocationPage }}"
                class="w-full border-0"
                style="height: 600px;"
                title="Locations Management"
            ></iframe>
        </div>
    </x-filament::section>
</x-filament-panels::page>
