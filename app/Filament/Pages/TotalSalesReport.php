<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class TotalSalesReport extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = null;
    
    protected static bool $shouldRegisterNavigation = false;
    
    protected static string $view = 'filament.pages.total-sales-report';
    
    protected static ?string $slug = null;
    
    protected ?string $maxContentWidth = 'full';

    public ?array $reportData = [];
    public $csvFile;

    public static function routes(Panel $panel): void
    {
        Route::get('/total-sales-report', static::class)
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName());
    }

    public static function getRelativeRouteName(): string
    {
        return 'total-sales-report';
    }

    public function getMaxContentWidth(): string
    {
        return MaxWidth::Full->value;
    }

    public function mount(): void
    {
        $this->loadReportData();
    }

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'reportData' => $this->reportData,
        ]);
    }

    protected function loadReportData(): void
    {
        // Load from uploaded CSV file
        $filePath = 'sales-reports/total-sales-report.csv';
        if (Storage::disk('public')->exists($filePath)) {
            try {
                $fullPath = Storage::disk('public')->path($filePath);
                $this->reportData = $this->parseCsv($fullPath);
            } catch (\Exception $e) {
                Log::error('Failed to load Total Sales report: ' . $e->getMessage());
            }
        }
    }

    protected function parseCsv(string $filePath): array
    {
        $data = [];
        if (($handle = fopen($filePath, "r")) !== false) {
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                $data[] = $row;
            }
            fclose($handle);
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Upload CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    DatePicker::make('month')
                        ->label('Month')
                        ->displayFormat('Y-m')
                        ->format('Y-m')
                        ->default(now()->format('Y-m'))
                        ->required(),
                    FileUpload::make('csvFile')
                        ->label('CSV File')
                        ->acceptedFileTypes(['text/csv', 'application/csv', 'text/plain'])
                        ->directory('sales-reports')
                        ->disk('public')
                        ->visibility('public')
                        ->required()
                        ->maxSize(10240), // 10MB
                ])
                ->action(function (array $data): void {
                    if (!empty($data['csvFile']) && !empty($data['month'])) {
                        // Create directory for the month if it doesn't exist
                        $monthDir = 'sales-reports/' . $data['month'];
                        if (!Storage::disk('public')->exists($monthDir)) {
                            Storage::disk('public')->makeDirectory($monthDir);
                        }
                        
                        // Move the file to a specific name in the month folder
                        $uploadedPath = $data['csvFile'];
                        $finalPath = $monthDir . '/total-sales-report.csv';
                        Storage::disk('public')->move($uploadedPath, $finalPath);
                        
                        $this->loadReportData();
                        
                        Notification::make()
                            ->title('CSV uploaded successfully')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }
}

