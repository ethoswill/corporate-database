<x-filament-panels::page>
    <div class="space-y-6 w-full" style="max-width: 100%; width: 100%;">
        <!-- Sorting Controls -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700">Sort by:</label>
                <select 
                    wire:model.live="sortBy"
                    class="text-sm border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white px-3 py-2 pr-8 min-w-[220px]"
                >
                    <option value="date_desc">Date - Newest to Oldest</option>
                    <option value="date_asc">Date - Oldest to Newest</option>
                    <option value="name_asc">Title A-Z</option>
                    <option value="name_desc">Title Z-A</option>
                </select>
            </div>
        </div>

        <!-- Collections Grid - 4 Wide Layout -->
        <div class="grid gap-6 w-full" style="grid-template-columns: repeat(4, minmax(0, 1fr));" wire:key="main-collections-grid">
            @php
                $favorites = array_filter($collections ?? [], fn($c) => ($c['is_favorite'] ?? false) == true);
                $regular = array_filter($collections ?? [], fn($c) => ($c['is_favorite'] ?? false) == false);
            @endphp

            @php
                $hasFavorites = count($favorites) > 0;
                $hasRegular = count($regular) > 0;
            @endphp

            @if($hasFavorites)
                <!-- Favorites Section -->
                <div class="col-span-4 mb-2">
                    <h3 class="text-lg font-semibold text-gray-900">Favorites</h3>
                </div>
                @foreach($favorites as $favCollection)
                    @include('filament.pages.partials.collection-card', ['collection' => $favCollection])
                @endforeach

                @if($hasRegular)
                    <div class="col-span-4 mb-2 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900">All Collections</h3>
                    </div>
                @endif
            @endif

            @forelse($regular as $collection)
                @include('filament.pages.partials.collection-card', ['collection' => $collection])
            @empty
                <div class="col-span-4 text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No collections</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new collection.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Edit Modal -->
    <x-filament::modal 
        id="edit-collection-modal" 
        width="md"
    >
        <x-slot name="heading">
            Edit Collection
        </x-slot>
        
        <form wire:submit.prevent="saveCollection">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Collection Name</label>
                    <input 
                        type="text" 
                        wire:model="editForm.name"
                        required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea 
                        wire:model="editForm.description"
                        rows="3"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    ></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Replace Image</label>
                    <input 
                        type="file" 
                        wire:model="editForm.image"
                        accept="image/*"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                    />
                </div>
            </div>

            <x-slot name="footer">
                <x-filament::button type="submit" color="primary">
                    Update
                </x-filament::button>
                <x-filament::button color="gray" wire:click="editingCollectionId = null" x-on:click="$dispatch('close-modal', { id: 'edit-collection-modal' })">
                    Cancel
                </x-filament::button>
            </x-slot>
        </form>
    </x-filament::modal>

    <!-- Upload Image Modal -->
    <x-filament::modal 
        id="upload-image-modal" 
        width="md"
    >
        <x-slot name="heading">
            Upload Image
        </x-slot>
        
        <form wire:submit.prevent="saveCollection">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <input 
                        type="file" 
                        wire:model="uploadForm.image"
                        accept="image/*"
                        required
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                    />
                </div>
            </div>

            <x-slot name="footer">
                <x-filament::button type="submit" color="primary">
                    Upload
                </x-filament::button>
                <x-filament::button color="gray" wire:click="uploadingCollectionId = null" x-on:click="$dispatch('close-modal', { id: 'upload-image-modal' })">
                    Cancel
                </x-filament::button>
            </x-slot>
        </form>
    </x-filament::modal>
</x-filament-panels::page>
