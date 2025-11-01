@php
    $coverPath = $collection['cover_image_path'] ?? null;
    if ($coverPath) {
        $coverPath = ltrim($coverPath, '/');
        $coverPath = str_replace('collections/', '', $coverPath);
        $fullPath = 'collections/' . $coverPath;
        $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($fullPath) 
            ? asset('storage/' . $fullPath) 
            : (asset('storage/' . $coverPath) ?? null);
    } else {
        $imageUrl = null;
    }
    $formattedDate = isset($collection['created_at']) ? \Carbon\Carbon::parse($collection['created_at'])->format('M d, Y') : '';
    $itemCount = ($collection['assets_count'] ?? 0) + ($collection['children_count'] ?? 0);
    $isFavorite = $collection['is_favorite'] ?? false;
@endphp
<div 
    class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 relative group"
    style="aspect-ratio: 3/5; width: 100%;"
    x-data="{ 
        showMenu: false,
        isEditing: false,
        collectionName: '{{ addslashes($collection['name'] ?? '') }}'
    }"
    @dblclick="$event.stopPropagation(); isEditing = true"
>
    <!-- Image Section -->
    <div class="relative bg-gray-100 overflow-hidden" style="height: 70%;">
        <a
            href="{{ \App\Filament\Pages\CollectionDetail::getUrl(['collection' => $collection['id'] ?? 0]) }}"
            class="absolute inset-0 z-0"
        ></a>
        @if($imageUrl)
            <img 
                src="{{ $imageUrl }}" 
                alt="{{ $collection['name'] ?? '' }}"
                class="w-full h-full object-cover cursor-pointer"
            />
        @else
            <div class="w-full h-full flex items-center justify-center bg-gray-200 cursor-pointer">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
        
        <!-- Favorite Star Button -->
        <button
            wire:click="toggleFavorite({{ $collection['id'] ?? 0 }})"
            @click.stop
            class="absolute top-2 left-2 bg-white rounded-full p-1.5 shadow-md hover:bg-gray-50 transition z-40"
            title="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}"
        >
            @if($isFavorite)
                <svg class="w-4 h-4 text-yellow-500 fill-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                </svg>
            @else
                <svg class="w-4 h-4 text-gray-400 hover:text-yellow-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
            @endif
        </button>
        
        <!-- Overlay with Upload Button -->
        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100 z-10">
            <button
                wire:click="openUploadModal({{ $collection['id'] ?? 0 }})"
                @click.stop
                class="bg-white text-gray-700 px-3 py-1 rounded text-xs font-medium hover:bg-gray-100 transition z-20"
            >
                {{ $imageUrl ? 'Replace' : 'Upload Image' }}
            </button>
        </div>
        
        <!-- Menu Button -->
        <button
            @click.stop="showMenu = !showMenu"
            class="absolute top-2 right-2 bg-white rounded-full p-1.5 shadow-md hover:bg-gray-50 transition z-30"
            style="opacity: 1;"
        >
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
            </svg>
        </button>
        
        <!-- Dropdown Menu -->
        <div 
            x-show="showMenu"
            x-cloak
            @click.away="showMenu = false"
            class="absolute top-10 right-2 bg-white rounded-lg shadow-lg py-1 z-30 min-w-[120px] border border-gray-200"
            style="display: none;"
        >
            <button
                wire:click="openEditModal({{ $collection['id'] ?? 0 }})"
                @click.stop="showMenu = false"
                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </button>
            <button
                wire:click="deleteCollection({{ $collection['id'] ?? 0 }})"
                wire:confirm="Are you sure you want to delete this collection?"
                @click.stop="showMenu = false"
                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
            </button>
        </div>
    </div>
    
    <!-- Text Section -->
    <div class="px-3 pt-2 pb-2 flex flex-col" style="height: 30%;">
        <div class="relative">
            <a
                href="{{ \App\Filament\Pages\CollectionDetail::getUrl(['collection' => $collection['id'] ?? 0]) }}"
                class="w-full text-sm font-semibold text-gray-900 bg-transparent border-none p-0 focus:outline-none cursor-pointer hover:text-blue-600 transition block mb-0"
                @click.stop
            >
                <span
                    x-show="!isEditing"
                    class="w-full text-sm font-semibold text-gray-900 cursor-pointer hover:text-blue-600 transition block"
                >
                    {{ $collection['name'] ?? '' }}
                </span>
            </a>
            <input
                type="text"
                x-model="collectionName"
                x-show="isEditing"
                @blur="isEditing = false; $wire.call('updateCollectionName', {{ $collection['id'] ?? 0 }}, collectionName)"
                @keyup.enter="isEditing = false; $wire.call('updateCollectionName', {{ $collection['id'] ?? 0 }}, collectionName)"
                class="w-full text-sm font-semibold text-gray-900 border border-blue-500 rounded px-1"
                autofocus
                x-cloak
                style="display: none;"
            />
        <div class="flex items-center justify-between text-xs text-gray-500" style="margin-top: 2px;">
            <span class="flex items-center gap-1">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                {{ $itemCount }} items
            </span>
            <span>{{ $formattedDate }}</span>
        </div>
    </div>
</div>

