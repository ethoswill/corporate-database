<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class SalesReportsWidget extends Widget
{
    protected static string $view = 'filament.widgets.sales-reports-widget';

    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        return true;
    }
}

