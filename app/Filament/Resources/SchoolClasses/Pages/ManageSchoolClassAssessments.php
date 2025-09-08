<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use BackedEnum;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\AssessmentType;
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
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassAssessments extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'assessments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Field::date('date'),

                Select::make('type')
                    ->options(AssessmentType::class)
                    ->required()
                    ->searchable(),

                TextInput::make('points')
                    ->helperText('Maximum points')
                    ->required()
                    ->placeholder('100')
                    ->numeric(),

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
                Column::text('name'),
                Column::text('date')->width('1%'),
                Column::enum('type', AssessmentType::class)->badge()->width('1%'),
                Column::text('points')->color('info')->width('1%'),
                Column::text('description')->toggleable(isToggledHiddenByDefault:true),
                Column::enum('status', AssessmentStatus::class)->width('1%')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::Medium)
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Medium),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
