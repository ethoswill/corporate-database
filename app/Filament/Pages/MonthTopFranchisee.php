<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class MonthTopFranchisee extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = null;
    
    protected static bool $shouldRegisterNavigation = false;
    
    protected static string $view = 'filament.pages.month-top-franchisee';
    
    protected static ?string $slug = null;
    
    protected ?string $maxContentWidth = 'full';

    public ?array $reportData = [];
    public ?string $month = null;

    public static function routes(Panel $panel): void
    {
        Route::get('/month-top-franchisee/{month}', static::class)
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName());
    }

    public static function getRelativeRouteName(): string
    {
        return 'month-top-franchisee';
    }

    public function getMaxContentWidth(): string
    {
        return MaxWidth::Full->value;
    }

    public function mount(?string $month = null): void
    {
        $this->month = $month;
        $this->loadReportData();
    }

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'reportData' => $this->reportData,
            'month' => $this->month,
            'monthName' => $this->formatMonthName($this->month),
        ]);
    }

    protected function formatMonthName(?string $date): string
    {
        if (!$date) {
            return 'Unknown';
        }
        
        try {
            $carbon = \Carbon\Carbon::createFromFormat('Y-m', $date);
            return $carbon->format('F Y');
        } catch (\Exception $e) {
            return $date;
        }
    }

    protected function loadReportData(): void
    {
        if (!$this->month) {
            return;
        }

        $filePath = 'sales-reports/' . $this->month . '/top-franchisee-report.csv';
        if (Storage::disk('public')->exists($filePath)) {
            try {
                $fullPath = Storage::disk('public')->path($filePath);
                $this->reportData = $this->parseCsv($fullPath);
            } catch (\Exception $e) {
                Log::error('Failed to load Top Franchisee report: ' . $e->getMessage());
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
                    if (!empty($data['csvFile']) && $this->month) {
                        // Create directory for the month if it doesn't exist
                        $monthDir = 'sales-reports/' . $this->month;
                        if (!Storage::disk('public')->exists($monthDir)) {
                            Storage::disk('public')->makeDirectory($monthDir);
                        }
                        
                        // Move the file to a specific name in the month folder
                        $uploadedPath = $data['csvFile'];
                        $finalPath = $monthDir . '/top-franchisee-report.csv';
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

