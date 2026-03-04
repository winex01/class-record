<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Services\Icon;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Filament\Fields\Select;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use App\Filament\Fields\Textarea;
use Filament\Support\Enums\Width;
use App\Filament\Fields\TextInput;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use App\Filament\Fields\DatePicker;
use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TextColumn;
use App\Enums\CompletedPendingStatus;
use Illuminate\Support\Facades\Blade;
use App\Filament\Fields\ToggleButtons;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Columns\BooleanColumn;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Columns\BooleanIconColumn;
use App\Filament\Resources\MyFiles\MyFileResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\AssessmentTypes\AssessmentTypeResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\RelationManagers\RecordScoreRelationManager;

class ManageSchoolClassAssessments extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'assessments';

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->count()
                ),

           CompletedPendingStatus::COMPLETED->getLabel() => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    // No student with a null score => all students have scores
                    $query->whereDoesntHave('students', function ($q) {
                        $q->whereNull('score');
                    })
                )
                ->badgeColor('info')
                ->badge(fn () =>
                    $this->getOwnerRecord()
                        ->{static::$relationship}()
                        ->whereDoesntHave('students', function ($q) {
                            $q->whereNull('score');
                        })
                        ->count()
                ),

            CompletedPendingStatus::PENDING->getLabel() => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereHas('students', function ($q) {
                        $q->whereNull('score'); // pivot score is null
                    })
                )
                ->badgeColor('danger')
                ->badge(fn () =>
                    $this->getOwnerRecord()
                        ->{static::$relationship}()
                        ->whereHas('students', function ($q) {
                            $q->whereNull('score');
                        })
                        ->count()
                ),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->placeholder('e.g., Quiz #1, Midterm Exam, Chapter 5 Test, etc.')
                            ->required()
                            ->maxLength(255),

                        Select::make('assessment_type_id')
                            ->relationship('assessmentType', 'name')
                            ->required()
                            ->createOptionForm(AssessmentTypeResource::getForm())
                            ->editOptionForm(AssessmentTypeResource::getForm()),

                        DatePicker::make('date'),

                        TextInput::make('max_score')
                            ->helperText('Highest points')
                            ->required()
                            ->placeholder('100')
                            ->numeric(),

                        Textarea::make('description')
                            ->rows(2)
                            ->placeholder('Additional notes or instructions...')
                    ])
                    ->columnSpan(1),

                Section::make()
                    ->schema([
                        MyFileResource::selectMyFileAndCreateOption(),
                        ToggleButtons::make('can_group_students')
                    ])
                    ->columnSpan(1),
            ])
            ->columns(2);
    }

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
            ->headerActions([
                SchoolClassResource::createAction($this->getOwnerRecord())
                    ->label('New Assessment'),

                static::getOverviewAction(),
            ])
            ->recordActions([
                RelationManagerAction::make('recordScoreRelationManager')
                    ->label('Score')
                    ->icon(Icon::students())
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
