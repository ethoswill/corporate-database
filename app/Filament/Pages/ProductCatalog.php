<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ProductCatalog extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Product Catalog';

    protected static ?int $navigationSort = 2;

    public function getHeading(): string
    {
        return 'Product Catalog';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return 'Product Catalog';
    }

    protected static string $view = 'filament.pages.coming-soon';

    protected function getViewData(): array
    {
        return [
            'pageTitle' => 'Product Catalog',
        ];
    }
}
