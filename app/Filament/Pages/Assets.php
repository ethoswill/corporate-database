<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Assets extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Assets';

    protected static ?string $navigationGroup = 'Main';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.assets';
}
