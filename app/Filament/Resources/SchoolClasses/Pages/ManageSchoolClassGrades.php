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
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
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

                // TODO::
                Repeater::make('components')
                    ->live()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('component_name')
                                    ->label('Component Name')
                                    ->helperText('e.g., Written Works, Performance, Midterm, Finals, etc.')
                                    ->required()
                                    ->maxLength(255)
                                    ->distinct()
                                    ->columnSpan(2),

                                TextInput::make('weighted_score')
                                    ->label('Weighted Score')
                                    ->helperText('Enter a value between 1 and 100')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->columnSpan(1),

                                CheckboxList::make('assessment_ids')
                                    ->label('Assign Assessments')
                                    ->bulkToggleable()
                                    ->searchable()
                                    ->required()
                                    ->distinct()
                                    ->columns(2)
                                    ->options(function (callable $get, $record, $set) {
                                        // Get all selected IDs from all repeater items
                                        $allSelected = collect($get('../../components'))
                                            ->pluck('assessment_ids')
                                            ->flatten()
                                            ->filter()
                                            ->all();

                                        // Get current item selected IDs (to exclude them from filtering)
                                        $currentSelected = collect($get('assessment_ids'))->all();

                                        // Compute the IDs that are selected in siblings (exclude current)
                                        $selectedInSiblings = array_diff($allSelected, $currentSelected);

                                        // Return only available options
                                        return Assessment::query()
                                            ->whereNotIn('id', $selectedInSiblings)
                                            ->pluck('name', 'id')
                                            ->mapWithKeys(fn($name, $id) => [(int) $id => $name])
                                            ->toArray();
                                    })
                                    ->descriptions(function () {
                                        return Assessment::query()
                                            ->with('assessmentType')
                                            ->get()
                                            ->mapWithKeys(function ($assessment) {
                                                return [
                                                    (int) $assessment->id =>
                                                        "Type: {$assessment->assessmentType->name}, Max Score: {$assessment->max_score}"
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->rules([
                                        fn ($get)=> function (string $attribute, $value, $fail) use ($get) {
                                            // $value = the current components repeater array
                                            $total = collect($get('../../components'))->sum('weighted_score');

                                            if ($total != 100) {
                                                $fail("The total weighted score of all components must equal 100%. Current total: {$total}%");
                                            }
                                        },
                                    ])
                                    ->columnSpan(3)
                            ]),
                    ])
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
