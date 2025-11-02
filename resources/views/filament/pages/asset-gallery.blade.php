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
            @if($collection)
                @php
                    // Build breadcrumb chain if collection has a parent
                    $breadcrumbs = [];
                    $current = $collection;
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

        <!-- Assets Grid - 4 Wide Layout -->
        <div class="grid gap-6 w-full" style="grid-template-columns: repeat(4, minmax(0, 1fr));" wire:key="assets-grid-{{ $collectionId }}">
            @forelse($assets ?? [] as $asset)
                @php
                    $fileUrl = asset('storage/' . $asset['file_path']);
                    $isVideo = isset($asset['file_type']) && str_starts_with($asset['file_type'], 'video/');
                @endphp
                <div 
                    class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-200 hover:shadow-lg relative group"
                    style="aspect-ratio: 3/4; width: 100%;"
                >
                    <!-- Media Section -->
                    <div class="relative bg-gray-100 overflow-hidden" style="height: 85%;">
                        @if($isVideo)
                            <video 
                                src="{{ $fileUrl }}" 
                                alt="{{ $asset['name'] ?? 'Video' }}"
                                class="w-full h-full object-cover"
                                muted
                            ></video>
                        @else
                            <img 
                                src="{{ $fileUrl }}" 
                                alt="{{ $asset['name'] ?? 'Image' }}"
                                class="w-full h-full object-cover"
                            />
                        @endif
                        
                        <!-- Action Buttons -->
                        <div class="absolute top-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition">
                            <button
                                wire:click="downloadAsset({{ $asset['id'] ?? 0 }})"
                                class="bg-white rounded-full p-1.5 shadow-md hover:bg-gray-50 transition"
                                title="Download {{ $isVideo ? 'video' : 'image' }}"
                            >
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                            </button>
                            <button
                                wire:click="deleteAsset({{ $asset['id'] ?? 0 }})"
                                wire:confirm="Are you sure you want to delete this {{ $isVideo ? 'video' : 'image' }}?"
                                class="bg-white rounded-full p-1.5 shadow-md hover:bg-gray-50 transition"
                                title="Delete {{ $isVideo ? 'video' : 'image' }}"
                            >
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Text Section -->
                    <div class="p-3" style="height: 15%;">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="text-sm font-medium text-gray-900 truncate">
                                {{ $asset['name'] ?? 'Untitled' }}
                            </span>
                            @if(isset($asset['file_size']))
                                <span class="text-xs text-gray-500 whitespace-nowrap">
                                    {{ number_format($asset['file_size'] / 1024, 1) }} KB
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-4 text-center py-12 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No media yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Upload your first media file to get started.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>

