<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Filament\Support\Enums\MaxWidth;

class MonthlyReport extends Page
{
    protected static ?string $navigationIcon = null;
    
    protected static bool $shouldRegisterNavigation = false;
    
    protected static string $view = 'filament.pages.monthly-report';
    
    protected static ?string $slug = null;
    
    protected ?string $maxContentWidth = 'full';

    public ?string $month = null;

    public static function routes(Panel $panel): void
    {
        Route::get('/monthly-report/{month}', static::class)
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName());
    }

    public static function getRelativeRouteName(): string
    {
        return 'monthly-report';
    }

    public function getMaxContentWidth(): string
    {
        return MaxWidth::Full->value;
    }

    public function mount(?string $month = null): void
    {
        $this->month = $month;
    }

    public function getHeading(): string
    {
        return $this->formatMonthName($this->month);
    }

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'month' => $this->month,
            'monthName' => $this->formatMonthName($this->month),
            'reports' => $this->getReports(),
            'adjacentMonths' => $this->getAdjacentMonths(),
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

    protected function getReports(): array
    {
        if (!$this->month) {
            return [];
        }

        $reports = [];
        $basePath = 'sales-reports/' . $this->month;
        
        $reportTypes = [
            'total-sales' => 'Total Sales Report',
            'top-product' => 'Top Product Report',
            'top-franchisee' => 'Top Franchisee Report',
        ];

        foreach ($reportTypes as $key => $label) {
            $filePath = $basePath . '/' . $key . '-report.csv';
            if (Storage::disk('public')->exists($filePath)) {
                $reports[$key] = [
                    'label' => $label,
                    'file' => $filePath,
                    'data' => $this->parseCsv(Storage::disk('public')->path($filePath)),
                ];
            }
        }

        return $reports;
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

    protected function getAdjacentMonths(): array
    {
        if (!$this->month) {
            return ['prev' => null, 'next' => null];
        }

        try {
            $currentMonth = \Carbon\Carbon::createFromFormat('Y-m', $this->month);
            $prevMonth = $currentMonth->copy()->subMonth();
            $nextMonth = $currentMonth->copy()->addMonth();
            
            // Check if folders exist
            $months = [];
            $directories = Storage::disk('public')->directories('sales-reports');
            $existingMonths = array_map('basename', $directories);
            
            $prevSlug = $prevMonth->format('Y-m');
            $nextSlug = $nextMonth->format('Y-m');
            
            return [
                'prev' => in_array($prevSlug, $existingMonths) ? $prevSlug : null,
                'next' => in_array($nextSlug, $existingMonths) ? $nextSlug : null,
            ];
        } catch (\Exception $e) {
            return ['prev' => null, 'next' => null];
        }
    }
}

