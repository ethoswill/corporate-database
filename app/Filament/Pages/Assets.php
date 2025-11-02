<?php

namespace App\Filament\Pages;

use App\Models\Collection;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class Assets extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Collections';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.assets';

    protected static ?string $title = 'Collections';

    protected static ?string $slug = 'collections';

    protected ?string $maxContentWidth = 'full';

    public function getMaxContentWidth(): string
    {
        return \Filament\Support\Enums\MaxWidth::Full->value;
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'collections' => $this->collections,
        ]);
    }

    public $collections = [];
    public $editingCollectionId = null;
    public $uploadingCollectionId = null;
    public $sortBy = 'date_desc'; // Default: newest to oldest
    
    // Form properties
    public $editForm = [
        'name' => '',
        'description' => '',
        'image' => null,
        'collection_id' => null,
    ];
    
    public $uploadForm = [
        'image' => null,
        'collection_id' => null,
        'name' => '',
    ];

    public function mount(): void
    {
        $this->sortBy = request()->query('sort', 'date_desc');
        $this->loadCollections();
    }

    public function updatedSortBy($value): void
    {
        $this->loadCollections();
    }

    public function loadCollections(): void
    {
        // Load favorites and regular collections separately
        $favoritesQuery = Collection::whereNull('parent_id')
            ->where('is_favorite', true)
            ->withCount(['assets', 'children']);

        $regularQuery = Collection::whereNull('parent_id')
            ->where('is_favorite', false)
            ->withCount(['assets', 'children']);

        // Apply sorting to both queries
        $sortFunction = function($query) {
            switch ($this->sortBy) {
                case 'date_desc':
                    return $query->orderBy('created_at', 'desc');
                case 'date_asc':
                    return $query->orderBy('created_at', 'asc');
                case 'name_asc':
                    return $query->orderBy('name', 'asc');
                case 'name_desc':
                    return $query->orderBy('name', 'desc');
                default:
                    return $query->orderBy('created_at', 'desc');
            }
        };

        $favorites = $sortFunction($favoritesQuery)->get()->toArray();
        $regular = $sortFunction($regularQuery)->get()->toArray();

        // Combine: favorites first, then regular collections
        $this->collections = array_merge($favorites, $regular);
    }

    public function toggleFavorite($collectionId): void
    {
        $collection = Collection::find($collectionId);
        
        if ($collection) {
            $collection->is_favorite = !$collection->is_favorite;
            $collection->save();
            
            $this->loadCollections();
        }
    }
    
    public function openEditModal($collectionId): void
    {
        $collection = Collection::find($collectionId);
        if ($collection) {
            $this->editForm = [
                'name' => $collection->name,
                'description' => $collection->description ?? '',
                'image' => null,
                'collection_id' => $collectionId,
            ];
            $this->editingCollectionId = $collectionId;
            $this->dispatch('open-modal', id: 'edit-collection-modal');
        }
    }
    
    public function openUploadModal($collectionId): void
    {
        $collection = Collection::find($collectionId);
        if ($collection) {
            $this->uploadForm = [
                'image' => null,
                'collection_id' => $collectionId,
                'name' => $collection->name,
            ];
            $this->uploadingCollectionId = $collectionId;
            $this->dispatch('open-modal', id: 'upload-image-modal');
        }
    }

    public function deleteCollection($id): void
    {
        $collection = Collection::find($id);
        
        if ($collection) {
            // Delete image if exists
            if ($collection->cover_image_path && Storage::disk('public')->exists($collection->cover_image_path)) {
                Storage::disk('public')->delete($collection->cover_image_path);
            }
            
            $collection->delete();
            
            Notification::make()
                ->title('Collection deleted successfully')
                ->success()
                ->send();
            
            $this->loadCollections();
        }
    }

    public function updateCollectionName($id, $name): void
    {
        $collection = Collection::find($id);
        
        if ($collection && $name) {
            $collection->name = $name;
            $collection->save();
            
            $this->loadCollections();
        }
    }

    public function saveCollection($data = null): void
    {
        // Handle form submission from Filament Action (data is array) or Livewire (data is null)
        if (is_null($data)) {
            // Use editForm if editing, otherwise use uploadForm (from wire:model)
            if ($this->editingCollectionId) {
                $this->validate([
                    'editForm.name' => 'required|string|max:255',
                    'editForm.description' => 'nullable|string',
                    'editForm.image' => 'nullable',
                    'editForm.collection_id' => 'required|exists:collections,id',
                ]);
                $data = $this->editForm;
            } else {
                $this->validate([
                    'uploadForm.image' => 'required',
                    'uploadForm.collection_id' => 'required|exists:collections,id',
                ]);
                $data = $this->uploadForm;
            }
        }

        $collectionId = $data['collection_id'] ?? null;
        
        if ($collectionId) {
            $collection = Collection::find($collectionId);
        } else {
            $collection = new Collection();
        }

        $collection->name = $data['name'] ?? ($collection->name ?? 'New Collection');
        $collection->description = $data['description'] ?? $collection->description;

        // Handle image upload
        if (isset($data['image']) && !empty($data['image'])) {
            // Delete old image if exists
            if ($collection->cover_image_path && Storage::disk('public')->exists($collection->cover_image_path)) {
                Storage::disk('public')->delete($collection->cover_image_path);
            }

            // Filament FileUpload component returns a string path when used in Action forms
            // Livewire wire:model returns a file object
            if (is_string($data['image'])) {
                // Filament already stored the file, just use the path
                // Remove 'collections/' prefix if present (Filament adds it)
                $collection->cover_image_path = ltrim($data['image'], 'collections/');
            } elseif (is_object($data['image']) && method_exists($data['image'], 'store')) {
                // Handle as uploaded file object (for Livewire wire:model forms)
                $path = $data['image']->store('collections', 'public');
                $collection->cover_image_path = $path;
            }
        }

        $collection->save();

        Notification::make()
            ->title($collectionId ? 'Collection updated successfully' : 'Collection created successfully')
            ->success()
            ->send();

        $this->loadCollections();
        $this->editingCollectionId = null;
        $this->uploadingCollectionId = null;
        
        // Close modals
        $this->dispatch('close-modal', id: 'edit-collection-modal');
        $this->dispatch('close-modal', id: 'upload-image-modal');
        
        // Reset forms
        $this->editForm = [
            'name' => '',
            'description' => '',
            'image' => null,
            'collection_id' => null,
        ];
        $this->uploadForm = [
            'image' => null,
            'collection_id' => null,
            'name' => '',
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Collection')
                ->icon('heroicon-o-plus-circle')
                ->form([
                    TextInput::make('name')
                        ->label('Collection Name')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3),
                    FileUpload::make('image')
                        ->label('Image')
                        ->image()
                        ->directory('collections')
                        ->disk('public')
                        ->visibility('public'),
                ])
                ->action(function (array $data): void {
                    $this->saveCollection($data);
                }),
        ];
    }

}
