<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Enums\AssessmentStatus;
use BackedEnum;
use App\Services\Field;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\AssessmentType;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
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

                TextInput::make('max_score')
                    ->required()
                    ->placeholder('100')
                    ->numeric(),

                Textarea::make('description')
                    ->rows(2)
                    ->placeholder('Optional..')
                    ->autosize(),

                Select::make('status')
                    ->options(AssessmentStatus::class)
                    ->default(AssessmentStatus::PENDING->value)
                    ->required()
                    ->searchable()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
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
