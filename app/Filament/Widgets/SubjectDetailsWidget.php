<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class SubjectDetailsWidget extends Widget
{
    protected string $view = 'filament.widgets.subject-details-widget';

    public ?Model $record = null;
    
    protected int | string | array $columnSpan = 'full';
    
    public static function canView(): bool
    {
        return true;
    }
}
