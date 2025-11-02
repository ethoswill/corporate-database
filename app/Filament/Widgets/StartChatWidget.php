<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class StartChatWidget extends Widget
{
    protected static string $view = 'filament.widgets.start-chat-widget';

    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        return true;
    }
}

