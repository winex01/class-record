<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Services\Field;
use App\Services\Column;
use App\Models\Assessment;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassGrades extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'grades';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->helperText('e.g., 1st Quarter, 1st Grading, Semi-Final, Final, etc.')
                    ->required()
                    ->maxLength(255),

                Field::tags('tags'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                Column::tags('tags'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->modalWidth(Width::TwoExtraLarge),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->modalWidth(Width::TwoExtraLarge),
                    EditAction::make()->modalWidth(Width::TwoExtraLarge),
                    DeleteAction::make(),
                ])->grouped()
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordAction('edit');
    }
}
