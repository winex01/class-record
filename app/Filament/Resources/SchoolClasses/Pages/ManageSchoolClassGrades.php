<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use App\Enums\GradeCompletionStatus;
use App\Filament\Columns\EnumColumn;
use App\Filament\Columns\TextColumn;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Facades\Blade;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Forms\SchoolClassGradeForm;
use App\Filament\Resources\SchoolClasses\Actions\GradingSettingActions;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassGradeActions;

class ManageSchoolClassGrades extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;
    protected static string $relationship = 'grades';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (!$this->getOwnerRecord()->gradingComponents()->exists()) {
            $this->dispatch('mountGradingSettings');
        }
    }

    #[On('mountGradingSettings')]
    public function openGradingSettings(): void
    {
        $this->mountTableAction('gradingSettingsAction');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(SchoolClassGradeForm::schema($this->getOwnerRecord()));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('gradeGradingComponents', 'schoolClass.gradingComponents');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('grading_period')
            ->defaultSort('sort', 'asc')
            ->columns([
                TextColumn::make('grading_period')
                    ->toggleable(false)
                    ->label('Grading Period')
                    ->color('primary')
                    ->size(TextSize::Large)
                    ->searchable(false)
                    ->sortable(false),

                EnumColumn::make('status')
                    ->toggleable(false)
                    ->enum(GradeCompletionStatus::class)
                    ->size(TextSize::Medium)
                    ->sortable(false)
                    ->searchable(false)
                    ->state(function ($record) {
                        return $record->isComplete
                            ? GradeCompletionStatus::COMPLETE->value
                            : GradeCompletionStatus::INCOMPLETE->value;
                    })
                    ->description(function ($record) {
                        $record->load('gradeGradingComponents');
                        $savedComponentIds = $record->gradeGradingComponents->pluck('grading_component_id');
                        $missing = $record->schoolClass->gradingComponents
                            ->whereNotIn('id', $savedComponentIds)
                            ->map(fn ($component) => $component->name)
                            ->join(', ');

                        return $missing
                            ? "Missing: {$missing}"
                            : null;
                    })
            ])
            ->recordActions([
                SchoolClassGradeActions::viewGradesAction($this->getOwnerRecord())
                    ->modalWidth(Width::Screen),
                ViewAction::make()->modalWidth(Width::TwoExtraLarge),
                EditAction::make()->modalWidth(Width::TwoExtraLarge),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()->label('New Grade')->modalWidth(Width::TwoExtraLarge),

                Action::make('finalGrades')
                    ->label('Final Grades')
                    ->color('info')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalWidth(Width::FiveExtraLarge)
                    ->modalHeading('Student Final Grades')
                    ->modalDescription(fn ($livewire) => 'Students final grades across all grading periods for ' . $livewire->getOwnerRecord()->name)
                    ->modalContent(fn ($livewire) => new HtmlString(
                        Blade::render(
                            '@livewire("final-grades", ["schoolClassId" => $schoolClassId])',
                            ['schoolClassId' => $livewire->getOwnerRecord()->id]
                        )
                    )),

                GradingSettingActions::action($this->getOwnerRecord()),
                DeleteBulkAction::make(),
            ])
            ->paginated(false)
            ->actionsAlignment('start')
            ->reorderable('sort', $this->getOwnerRecord()->active);
    }
}
