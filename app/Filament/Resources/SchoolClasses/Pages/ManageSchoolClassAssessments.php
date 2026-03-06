<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TextColumn;
use App\Enums\CompletedPendingStatus;
use Illuminate\Support\Facades\Blade;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Columns\BooleanColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Columns\BooleanIconColumn;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassActions;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\Forms\SchoolClassAssessmentForm;
use App\Filament\Resources\SchoolClasses\Filters\SchoolClassAssessmentFilters;
use App\Filament\Resources\SchoolClasses\RelationManagers\RecordScoreRelationManager;

class ManageSchoolClassAssessments extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'assessments';

    public function getTabs(): array
    {
        return SchoolClassAssessmentFilters::getTabs($this->getOwnerRecord());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(SchoolClassAssessmentForm::schema())
            ->columns(2);
    }

    // TODO:: to be continued here!!
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                ...static::getColumns(),

                BooleanIconColumn::make('status')
                    ->getStateUsing(fn ($record) =>
                        !$record->students()
                            ->whereNull('score')
                            ->exists()
                    )
                    ->tooltip(function ($record) {
                        $status = $record->students()
                            ->whereNull('score')
                            ->exists();

                        return $status ? CompletedPendingStatus::PENDING->getLabel() : CompletedPendingStatus::COMPLETED->getLabel();
                    })
                    ->sortable(
                        query: fn ($query, string $direction) =>
                            $query->withExists([
                                'students as has_pending' => fn ($q) => $q->whereNull('score')
                            ])
                            ->orderBy('has_pending', $direction)
                    )

            ])
            ->filters([
                ...static::getFilters(),
            ])
            ->recordActions([
                RelationManagerAction::make('recordScoreRelationManager')
                    ->label('Score')
                    ->icon(StudentResource::getNavigationIcon())
                    ->color('info')
                    ->slideOver()
                    ->relationManager(RecordScoreRelationManager::make())
                    ->modalDescription(fn ($record) => new HtmlString(
                        view('filament.components.assessment-modal-heading', [
                            'record' => $record,
                        ])->render()
                    )),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                SchoolClassActions::createWithStudentsAction($this->getOwnerRecord())
                    ->label('New Assessment'),
                static::getOverviewAction(),
                DeleteBulkAction::make(),
            ])
            ->recordAction('recordScoreRelationManager');
    }

    public static function getFilters()
    {
        return [
            SelectFilter::make('assessmentType')
                ->relationship('assessmentType', 'name')
                ->multiple()
                ->searchable()
                ->preload()
        ];
    }

    public static function getColumns()
    {
        return [
            TextColumn::make('name'),

            TextColumn::make('assessmentType.name')
                ->label('Type')
                ->color('primary'),

            DateColumn::make('date'),

            'max_score' =>
            TextColumn::make('max_score')
                ->label('Max')
                ->alignCenter()
                ->color('info')
                ->tooltip('Max score'),

            TextColumn::make('description')
                ->toggleable(isToggledHiddenByDefault:true),

            BooleanColumn::make('can_group_students')
                ->toggleable(isToggledHiddenByDefault:true)
                ->label('Can group')
        ];
    }

    public static function getOverviewAction(): Action
    {
        return Action::make('overview')
            ->color('info')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalWidth(Width::TwoExtraLarge)
            ->modalHeading('Student Assessment Overview')
            ->modalDescription(fn ($livewire) => 'Overview of students across all assessment records for ' . $livewire->getOwnerRecord()->name)
            ->modalContent(fn ($livewire) => new HtmlString(
                Blade::render(
                    '@livewire("assessment-overview", ["schoolClassId" => $schoolClassId])',
                    ['schoolClassId' => $livewire->getOwnerRecord()->id]
                )
            ));
    }
}
