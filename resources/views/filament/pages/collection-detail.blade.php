<x-filament-panels::page>
    <div class="space-y-6 w-full" style="max-width: 100%; width: 100%;">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-4 flex-wrap">
            <a 
                href="{{ \App\Filament\Pages\Assets::getUrl() }}"
                class="hover:text-gray-900 transition"
            >
                Collections
            </a>
            @if($parentCollection)
                @php
                    $breadcrumbs = [];
                    $current = $parentCollection;
                    while ($current) {
                        $breadcrumbs[] = $current;
                        $current = $current->parent;
                    }
                    $breadcrumbs = array_reverse($breadcrumbs);
                @endphp
                @foreach($breadcrumbs as $index => $crumb)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    @if($index < count($breadcrumbs) - 1)
                        <a 
                            href="{{ \App\Filament\Pages\CollectionDetail::getUrl(['collection' => $crumb->id]) }}"
                            class="hover:text-gray-900 transition"
                        >
                            {{ $crumb->name }}
                        </a>
                    @else
                        <span class="text-gray-900 font-medium">{{ $crumb->name }}</span>
                    @endif
                @endforeach
            @endif
        </div>

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
        <div class="grid gap-6" style="grid-template-columns: repeat(4, minmax(0, 1fr));" wire:key="collections-grid-{{ $collectionId }}">
            @php
                $favorites = array_filter($collections ?? [], fn($c) => ($c['is_favorite'] ?? false) == true);
                $regular = array_filter($collections ?? [], fn($c) => ($c['is_favorite'] ?? false) == false);
            @endphp

            @if(count($favorites) > 0)
                <!-- Favorites Section -->
                <div class="col-span-4 mb-2">
                    <h3 class="text-lg font-semibold text-gray-900">Favorites</h3>
                </div>
                @foreach($favorites as $favCollection)
                    @include('filament.pages.partials.collection-card', ['collection' => $favCollection])
                @endforeach

                @if(count($regular) > 0)
                    <div class="col-span-4 mb-2 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900">All Folders</h3>
                    </div>
                @endif
            @endif

            @forelse($regular as $collection)
                @include('filament.pages.partials.collection-card', ['collection' => $collection])
            @empty
                <div class="col-span-4 text-center py-12 text-gray-500 dark:text-gray-400">
                    <p class="text-sm">No folders yet. Create your first folder to get started.</p>
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

