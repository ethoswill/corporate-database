<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class LaunchCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Launch Calendar';

    protected static ?string $navigationGroup = 'Main';

    protected static ?int $navigationSort = 3;

    public function getHeading(): string
    {
        return 'Launch Calendar';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return 'Launch Calendar';
    }

    protected static string $view = 'filament.pages.coming-soon';

    protected function getViewData(): array
    {
        return [
            'pageTitle' => 'Launch Calendar',
        ];
    }
}
