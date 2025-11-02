<?php

namespace App\Filament\Pages;

use App\Models\Asset;
use App\Models\Collection;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class AssetGallery extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = null;
    
    protected static bool $shouldRegisterNavigation = false;
    
    protected static string $view = 'filament.pages.asset-gallery';
    
    protected static ?string $slug = null;
    
    protected ?string $maxContentWidth = 'full';

    public function getMaxContentWidth(): string
    {
        return MaxWidth::Full->value;
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'assets' => $this->assets,
            'collection' => $this->collection,
            'collectionId' => $this->collectionId,
        ]);
    }

    public $collectionId;
    public $collection = null;
    public $assets = [];
    public $editingAssetId = null;
    public $bulkUpload = [];

    public function mount($collection = null): void
    {
        $this->collectionId = $collection ?? request()->route('collection') ?? request()->query('collection');
        
        if (!$this->collectionId) {
            redirect()->route('filament.admin.pages.collections')->send();
            return;
        }
        
        $this->loadCollection();
        $this->loadAssets();
    }

    protected function loadCollection(): void
    {
        $this->collection = Collection::with('parent')->find($this->collectionId);
        
        if (!$this->collection) {
            Notification::make()
                ->title('Collection not found')
                ->danger()
                ->send();
            
            redirect()->route('filament.admin.pages.collections')->send();
            return;
        }
        
        // Load full parent chain recursively for breadcrumbs
        $this->loadParentChain($this->collection);
    }
    
    protected function loadParentChain(Collection $collection): void
    {
        if ($collection->parent_id && !$collection->relationLoaded('parent')) {
            $collection->load('parent');
        }
        
        if ($collection->parent) {
            $this->loadParentChain($collection->parent);
        }
    }

    public function loadAssets(): void
    {
        $this->assets = Asset::where('collection_id', $this->collectionId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function deleteAsset($assetId): void
    {
        $asset = Asset::find($assetId);
        
        if ($asset) {
            // Delete file if exists
            if ($asset->file_path && Storage::disk('public')->exists($asset->file_path)) {
                Storage::disk('public')->delete($asset->file_path);
            }
            
            $asset->delete();
            
            Notification::make()
                ->title('Media deleted successfully')
                ->success()
                ->send();
            
            $this->loadAssets();
        }
    }

    public function downloadAsset($assetId)
    {
        $asset = Asset::find($assetId);
        
        if (!$asset || !$asset->file_path) {
            Notification::make()
                ->title('File not found')
                ->danger()
                ->send();
            
            return null;
        }

        $fullPath = Storage::disk('public')->path($asset->file_path);
        
        if (!file_exists($fullPath)) {
            Notification::make()
                ->title('File not found on disk')
                ->danger()
                ->send();
            
            return null;
        }

        return Response::download($fullPath, $asset->file_name);
    }

    public function downloadAllAssets()
    {
        $assets = Asset::where('collection_id', $this->collectionId)->get();
        
        if ($assets->isEmpty()) {
            Notification::make()
                ->title('No files to download')
                ->warning()
                ->send();
            
            return null;
        }

        $zipPath = storage_path('app/temp_' . uniqid() . '.zip');
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Notification::make()
                ->title('Failed to create zip file')
                ->danger()
                ->send();
            
            return null;
        }

        $addedCount = 0;
        foreach ($assets as $asset) {
            if ($asset->file_path && Storage::disk('public')->exists($asset->file_path)) {
                $fullPath = Storage::disk('public')->path($asset->file_path);
                $zip->addFile($fullPath, $asset->file_name);
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($zipPath);
            Notification::make()
                ->title('No valid files found to download')
                ->warning()
                ->send();
            
            return null;
        }

        $collectionName = str_replace([' ', '/'], ['_', '_'], $this->collection->name ?? 'collection');
        $zipFileName = $collectionName . '_' . date('Y-m-d') . '.zip';

        return Response::download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function saveAsset($data): void
    {
        $assetId = $data['id'] ?? null;
        
        if ($assetId) {
            $asset = Asset::find($assetId);
        } else {
            $asset = new Asset();
            $asset->collection_id = $this->collectionId;
        }

        $asset->name = $data['name'] ?? 'Untitled';
        $asset->description = $data['description'] ?? null;

        // Handle file upload
        if (isset($data['file_path']) && !empty($data['file_path'])) {
            // Delete old file if exists
            if ($asset->file_path && Storage::disk('public')->exists($asset->file_path)) {
                Storage::disk('public')->delete($asset->file_path);
            }

            // Filament FileUpload component returns a string path when used in Action forms
            // Livewire wire:model returns a file object
            if (is_string($data['file_path'])) {
                $asset->file_path = $data['file_path'];
                $asset->file_name = basename($data['file_path']);
                
                // Get file type and size
                $fullPath = Storage::disk('public')->path($asset->file_path);
                if (file_exists($fullPath)) {
                    $asset->file_type = mime_content_type($fullPath);
                    $asset->file_size = filesize($fullPath);
                }
            } elseif (is_object($data['file_path']) && method_exists($data['file_path'], 'store')) {
                $path = $data['file_path']->store('assets', 'public');
                $asset->file_path = $path;
                $asset->file_name = $data['file_path']->getClientOriginalName();
                $asset->file_type = $data['file_path']->getMimeType();
                $asset->file_size = $data['file_path']->getSize();
            }
        }

        $asset->save();

        Notification::make()
            ->title($assetId ? 'Media updated successfully' : 'Media uploaded successfully')
            ->success()
            ->send();

        $this->loadAssets();
        $this->editingAssetId = null;
    }

    public function uploadBulkAssets(): void
    {
        // Filament FileUpload with multiple() returns an array of UUID => path mappings
        $filePaths = is_array($this->bulkUpload) ? $this->bulkUpload : [];
        
        if (empty($filePaths)) {
            Notification::make()
                ->title('No files selected')
                ->danger()
                ->send();
            return;
        }

        $uploadedCount = 0;

        foreach ($filePaths as $uuid => $filePath) {
            // Skip if not a string (could be TemporaryUploadedFile)
            if (!is_string($filePath)) {
                continue;
            }
            
            // $filePath is a string path like "assets/filename.jpg"
            $fullPath = Storage::disk('public')->path($filePath);
            
            if (!file_exists($fullPath)) {
                continue;
            }

            $asset = new Asset();
            $asset->collection_id = $this->collectionId;
            $asset->name = basename($filePath);
            $asset->file_path = $filePath;
            $asset->file_name = basename($filePath);
            $asset->file_type = mime_content_type($fullPath);
            $asset->file_size = filesize($fullPath);
            
            $asset->save();
            $uploadedCount++;
        }

        $this->bulkUpload = [];
        $this->loadAssets();

        Notification::make()
            ->title("Successfully uploaded {$uploadedCount} media file(s)")
            ->success()
            ->send();
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('downloadAll')
                ->label('Download All')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->downloadAllAssets()),
            Action::make('bulkUpload')
                ->label('Bulk Upload Media')
                ->icon('heroicon-o-photo')
                ->form([
                    FileUpload::make('bulkUpload')
                        ->label('Media Files (Images & Videos)')
                        ->multiple()
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->directory('assets')
                        ->disk('public')
                        ->visibility('public')
                        ->required()
                        ->maxFiles(50),
                ])
                ->action(function (array $data): void {
                    // Filament FileUpload with multiple() and directory() returns array of paths
                    $this->bulkUpload = $data['bulkUpload'] ?? [];
                    $this->uploadBulkAssets();
                }),
            Action::make('upload')
                ->label('Upload Single Media')
                ->icon('heroicon-o-cloud-arrow-up')
                ->form([
                    FileUpload::make('file_path')
                        ->label('Media File')
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->directory('assets')
                        ->disk('public')
                        ->visibility('public')
                        ->required(),
                    TextInput::make('name')
                        ->label('Name')
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->saveAsset($data);
                }),
        ];
    }

    public static function routes(Panel $panel): void
    {
        Route::get('/collections/{collection}/gallery', static::class)
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName());
    }

    public static function getRelativeRouteName(): string
    {
        return 'asset-gallery';
    }

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null): string
    {
        $collectionId = $parameters['collection'] ?? $parameters['id'] ?? null;
        
        if (!$collectionId) {
            return parent::getUrl($parameters, $isAbsolute, $panel, $tenant);
        }
        
        return route('filament.admin.pages.asset-gallery', ['collection' => $collectionId], $isAbsolute);
    }
}

