<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use BackedEnum;
use App\Services\Icon;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Enums\AssessmentStatus;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\MyFiles\MyFileResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\AssessmentTypes\AssessmentTypeResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\RelationManagers\Assessments\RecordScoreRelationManager;

class ManageSchoolClassAssessments extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'assessments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('assessment_type_id')
                    ->relationship( 'assessmentType', 'name')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->createOptionForm(AssessmentTypeResource::getForm())
                    ->editOptionForm(AssessmentTypeResource::getForm()),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Field::date('date'),

                TextInput::make('points')
                    ->helperText('Maximum points')
                    ->required()
                    ->placeholder('100')
                    ->numeric(),

                Select::make('my_file_id')
                    ->relationship( 'myFile', 'name')
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

                Textarea::make('description')
                    ->rows(2)
                    ->placeholder('Additional notes or instructions...')
                    ->autosize(),

                ToggleButtons::make('status')
                    ->options(AssessmentStatus::class)
                    ->default(AssessmentStatus::PENDING->value)
                    ->inline()
                    ->grouped()

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Column::text('assessmentType.name')->badge()->width('1%')->label('Type'),
                Column::text('name'),
                Column::text('date')->width('1%'),
                Column::text('points')->color('info')->width('1%'),
                Column::text('description')->toggleable(isToggledHiddenByDefault:true),
                Column::enum('status', AssessmentStatus::class)->width('1%')
            ])
            ->filters([
                // TODO::
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::Medium)
            ])
            ->recordActions([
                RelationManagerAction::make('recordScoreRelationManager')
                    ->label('Score')
                    ->icon(Icon::students())
                    ->color('info')
                    ->slideOver()
                    ->relationManager(RecordScoreRelationManager::make()),

                EditAction::make()
                    ->modalWidth(Width::Medium),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
