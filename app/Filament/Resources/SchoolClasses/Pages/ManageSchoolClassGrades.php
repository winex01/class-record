<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use Livewire\Attributes\On;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TextColumn;
use Filament\Support\Enums\TextSize;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Columns\BooleanIconColumn;
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('grading_period')
            ->defaultSort('grading_period', 'asc')
            ->columns([
                TextColumn::make('grading_period')
                    ->label('Grading Period')
                    ->color('primary')
                    ->size(TextSize::Large)
                    ->searchable(false),

                BooleanIconColumn::make('status')
                    ->width(null)
                    ->state(function ($record) {
                        $record->load('gradeGradingComponents.assessments');

                        $totalComponents = $record->schoolClass->gradingComponents()->count();

                        // Not all components have been saved yet
                        if ($record->gradeGradingComponents->count() !== $totalComponents) {
                            return false;
                        }

                        // All saved, now check each has assessments
                        return $record->gradeGradingComponents->every(
                            fn ($component) => $component->assessments->isNotEmpty()
                        );
                    })
            ])
            ->paginated(false)
            ->actionsAlignment('start')
            ->recordActions([
                SchoolClassGradeActions::viewGradesAction($this->getOwnerRecord()),
                ViewAction::make()->modalWidth(Width::TwoExtraLarge),
                EditAction::make()->modalWidth(Width::TwoExtraLarge),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()->label('New Grade')->modalWidth(Width::TwoExtraLarge),
                GradingSettingActions::action($this->getOwnerRecord()),
                DeleteBulkAction::make(),
            ]);
            // ->recordAction('grades');
    }
}
