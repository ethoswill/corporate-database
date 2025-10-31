<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Assets extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Assets';

    protected static ?string $navigationGroup = 'Main';

    protected static ?int $navigationSort = 4;

    public function getHeading(): string
    {
        return 'Assets';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return 'Assets';
    }

    protected static string $view = 'filament.pages.coming-soon';

    protected function getViewData(): array
    {
        return [
            'pageTitle' => 'Assets',
        ];
    }
}
