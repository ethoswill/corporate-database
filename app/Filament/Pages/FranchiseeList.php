<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LocationResource;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class FranchiseeList extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Franchisee List';

    protected static ?string $navigationGroup = 'Main';

    protected static ?int $navigationSort = 6;

    public function mount(): void
    {
        // Redirect to LocationResource index page
        redirect(LocationResource::getUrl('index'));
    }

    public function getHeading(): string | Htmlable
    {
        return 'My Locations';
    }

    public static function getNavigationLabel(): string
    {
        return 'Franchisee List';
    }
}
