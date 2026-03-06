<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\SchoolClasses\Forms\SchoolClassForm;

class SubjectDetailsWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.widgets.subject-details-widget';

    public ?Model $record = null;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }

    public function editClassAction(): EditAction
    {
        return EditAction::make('editClassAction')
            ->record($this->record)
            ->label('Edit')
            ->link()
            ->icon('heroicon-m-pencil-square')
            ->form(SchoolClassForm::schema())
            ->modalWidth(Width::Large)
            ->after(fn () => $this->dispatch('refreshTable'));
    }
}
