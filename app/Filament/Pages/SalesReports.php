<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SalesReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Sales Reports';

    protected static ?string $navigationGroup = 'Main';

    protected static ?int $navigationSort = 5;

    public function getHeading(): string
    {
        return 'Sales Reports';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return 'Sales Reports';
    }

    protected static string $view = 'filament.pages.coming-soon';

    protected function getViewData(): array
    {
        return [
            'pageTitle' => 'Sales Reports',
        ];
    }
}
