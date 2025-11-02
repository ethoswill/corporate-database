<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Livewire\WithFileUploads;

class SalesReports extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Sales Reports';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.sales-reports';

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'months' => $this->getMonths(),
            'summary' => $this->getSummaryData(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createMonth')
                ->label('Create Month Folder')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->form([
                    Select::make('month_value')
                        ->label('Month')
                        ->options(function () {
                            $months = [];
                            foreach (range(1, 12) as $month) {
                                $months[$month] = now()->setMonth($month)->setDay(1)->format('F');
                            }
                            return $months;
                        })
                        ->default(now()->month)
                        ->required(),
                    Select::make('year')
                        ->label('Year')
                        ->options(function () {
                            $years = [];
                            $currentYear = (int) now()->year;
                            foreach (range($currentYear, $currentYear + 5) as $year) {
                                $years[$year] = (string) $year;
                            }
                            return $years;
                        })
                        ->default(now()->year)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    // Format month as YYYY-MM for directory name, but display as "Month YYYY"
                    $monthDir = 'sales-reports/' . $data['year'] . '-' . str_pad($data['month_value'], 2, '0', STR_PAD_LEFT);
                    
                    // Create directory for the month if it doesn't exist
                    if (!Storage::disk('public')->exists($monthDir)) {
                        Storage::disk('public')->makeDirectory($monthDir);
                    }
                    
                    Notification::make()
                        ->title('Month folder created successfully')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getMonths(): array
    {
        $months = [];
        $directories = Storage::disk('public')->directories('sales-reports');
        
        foreach ($directories as $dir) {
            if (preg_match('/\d{4}-\d{2}$/', basename($dir))) {
                $months[] = [
                    'path' => $dir,
                    'name' => $this->formatMonthName(basename($dir)),
                    'slug' => basename($dir),
                ];
            }
        }
        
        // Sort by date descending
        usort($months, function($a, $b) {
            return strcmp($b['slug'], $a['slug']);
        });
        
        return $months;
    }

    protected function formatMonthName(string $date): string
    {
        try {
            $carbon = Carbon::createFromFormat('Y-m', $date);
            return $carbon->format('F Y');
        } catch (\Exception $e) {
            return $date;
        }
    }

    protected function getSummaryData(): array
    {
        $totalSales = 0;
        $totalRebated = 0;
        $topProducts = [];
        $topFranchisees = [];
        
        $directories = Storage::disk('public')->directories('sales-reports');
        
        foreach ($directories as $dir) {
            if (!preg_match('/\d{4}-\d{2}$/', basename($dir))) {
                continue;
            }
            
            // Read total sales
            $totalSalesFile = $dir . '/total-sales-report.csv';
            if (Storage::disk('public')->exists($totalSalesFile)) {
                $data = $this->parseCsv(Storage::disk('public')->path($totalSalesFile));
                if (!empty($data) && count($data) > 1) {
                    // Assuming the total is in the first data row, first column
                    $salesValue = $data[1][0] ?? '0';
                    // Remove $ and commas
                    $salesValue = str_replace(['$', ','], '', $salesValue);
                    if (is_numeric($salesValue)) {
                        $totalSales += (float) $salesValue;
                    }
                    
                    // Check for rebated amount in second column
                    if (isset($data[1][1])) {
                        $rebatedValue = str_replace(['$', ','], '', $data[1][1]);
                        if (is_numeric($rebatedValue)) {
                            $totalRebated += (float) $rebatedValue;
                        }
                    }
                }
            }
            
            // Read top products
            $topProductsFile = $dir . '/top-product-report.csv';
            if (Storage::disk('public')->exists($topProductsFile)) {
                $data = $this->parseCsv(Storage::disk('public')->path($topProductsFile));
                if (!empty($data) && count($data) > 1) {
                    foreach (array_slice($data, 1, 3) as $row) {
                        if (!empty($row[0])) {
                            $topProducts[] = [
                                'name' => $row[0],
                                'amount' => $row[1] ?? 0,
                            ];
                        }
                    }
                }
            }
            
            // Read top franchisees
            $topFranchiseesFile = $dir . '/top-franchisee-report.csv';
            if (Storage::disk('public')->exists($topFranchiseesFile)) {
                $data = $this->parseCsv(Storage::disk('public')->path($topFranchiseesFile));
                if (!empty($data) && count($data) > 1) {
                    foreach (array_slice($data, 1) as $row) {
                        if (!empty($row[0])) {
                            $topFranchisees[] = [
                                'name' => $row[0],
                                'amount' => $row[1] ?? 0,
                            ];
                        }
                    }
                }
            }
        }
        
        // Aggregate franchisee totals
        $franchiseeTotals = [];
        foreach ($topFranchisees as $franchisee) {
            $name = $franchisee['name'];
            $amount = (float) str_replace(['$', ','], '', $franchisee['amount']);
            
            if (!isset($franchiseeTotals[$name])) {
                $franchiseeTotals[$name] = 0;
            }
            $franchiseeTotals[$name] += $amount;
        }
        
        // Sort by total and get top 5
        arsort($franchiseeTotals);
        $topFranchiseesAggregated = [];
        foreach (array_slice($franchiseeTotals, 0, 5, true) as $name => $total) {
            $topFranchiseesAggregated[] = [
                'name' => $name,
                'amount' => $total,
            ];
        }
        
        return [
            'total_sales' => '$' . number_format($totalSales, 2),
            'total_rebated' => '$' . number_format($totalRebated, 2),
            'top_products' => $topProducts,
            'top_franchisees' => $topFranchiseesAggregated,
        ];
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
}
