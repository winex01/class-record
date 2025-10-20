<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Enums\AssessmentStatus;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\MyFiles\MyFileResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\AssessmentTypes\AssessmentTypeResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\RelationManagers\RecordScoreRelationManager;

class ManageSchoolClassAssessments extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'assessments';

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->count()
                ),

                // TODO::
            // AssessmentStatus::COMPLETED->getLabel() => Tab::make()
            //     ->modifyQueryUsing(fn (Builder $query) => $query->where('status', AssessmentStatus::COMPLETED->value))
            //     ->badgeColor('info')
            //     ->badge(fn () =>
            //         $this->getOwnerRecord()->{static::$relationship}()->where('status', AssessmentStatus::COMPLETED->value)->count()
            //     ),

            // AssessmentStatus::PENDING->getLabel() => Tab::make()
            //     ->modifyQueryUsing(fn (Builder $query) => $query->where('status', AssessmentStatus::PENDING->value))
            //     ->badgeColor('danger')
            //     ->badge(fn () =>
            //         $this->getOwnerRecord()->{static::$relationship}()->where('status', AssessmentStatus::PENDING->value)->count()
            //     )
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
                            ->preload()
                            ->searchable()
                            ->createOptionForm(AssessmentTypeResource::getForm())
                            ->editOptionForm(AssessmentTypeResource::getForm()),

                        Field::date('date'),

                        TextInput::make('max_score')
                            ->helperText('Highest points')
                            ->required()
                            ->placeholder('100')
                            ->numeric(),

                        Textarea::make('description')
                            ->rows(2)
                            ->placeholder('Additional notes or instructions...')
                            ->autosize(),
                    ])
                    ->columnSpan(1),

                Section::make()
                    ->schema([
                        Select::make('my_file_id')
                            ->relationship('myFile', 'name')
                            ->helperText('Optional')
                            ->preload()
                            ->searchable()
                            ->editOptionForm(MyFileResource::getForm(true))
                            ->editOptionAction(function (Action $action) {
                                return $action
                                    ->icon('heroicon-o-eye')
                                    ->tooltip('View')
                                    ->modalHeading('View File Details')
                                    ->modalWidth(Width::Medium)
                                    ->modalSubmitAction(false)
                                    ->modalCancelActionLabel('Close');
                            }),

                        ToggleButtons::make('can_group_students')
                            ->inline()
                            ->default(false)
                            ->boolean(),
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
                Column::text('name'),
                Column::text('assessmentType.name')->badge()->width('1%')->label('Type'),
                Column::text('date')->width('1%'),
                Column::text('max_score')->label('Max')->color('info')->width('1%')->tooltip('Max score'),
                Column::text('description')->toggleable(isToggledHiddenByDefault:true),
                Column::boolean('can_group_students')->label('Can group')->toggleable(isToggledHiddenByDefault:true),

                Column::icon('status')
                    ->getStateUsing(fn ($record) =>
                        $record->students()
                            ->whereNull('score')
                            ->exists()
                    )
                    ->tooltip(function ($record) {
                        $status = $record->students()
                            ->whereNull('score')
                            ->exists();

                        return $status ? 'Pending' : 'Completed';
                    })

            ])
            ->filters([
                SelectFilter::make('assessmentType')
                    ->relationship('assessmentType', 'name')
                    ->multiple(),
            ])
            ->headerActions([
                SchoolClassResource::createAction($this->getOwnerRecord())
            ])
            ->recordActions([
                ActionGroup::make([
                    RelationManagerAction::make('recordScoreRelationManager')
                        ->label('Record Score')
                        ->icon(\App\Services\Icon::students())
                        ->color('info')
                        ->slideOver()
                        ->relationManager(RecordScoreRelationManager::make()),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])->grouped()
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordAction('recordScoreRelationManager');
    }
}
