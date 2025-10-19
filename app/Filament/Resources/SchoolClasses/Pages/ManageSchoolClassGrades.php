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

                CheckboxList::make('assessment_ids')
                    ->label('Assign Assessments')
                    ->options(function ($record) {
                        $query = Assessment::query()->whereNull('grade_id');

                        if ($record && $record->id) {
                            $query->orWhere('grade_id', $record->id);
                        }

                        return $query->pluck('name', 'id')->mapWithKeys(fn($name, $id) => [(int) $id => $name])->toArray();
                    })
                    ->columns(2)
                    ->bulkToggleable()
                    ->searchable()
                    ->required()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                        if ($record && $record->id) {
                            $ids = $record->assessments()->pluck('id')->map(fn($id) => (int) $id)->toArray();
                            $component->state($ids);
                        }
                    })
                    ->saveRelationshipsUsing(function (CheckboxList $component, $state, $record) {
                        if (!$record || !$record->id) return;

                        $state = array_map('intval', $state ?? []);

                        // Unassign unchecked ones
                        Assessment::where('grade_id', $record->id)
                            ->whereNotIn('id', $state)
                            ->update(['grade_id' => null]);

                        // Assign newly checked ones
                        if (!empty($state)) {
                            Assessment::whereIn('id', $state)->update(['grade_id' => $record->id]);
                        }
                    })
                    ->descriptions(function ($record) {
                        $query = Assessment::query()->whereNull('grade_id');

                        if ($record && $record->id) {
                            $query->orWhere('grade_id', $record->id);
                        }

                        return $query->with('assessmentType')->get()->mapWithKeys(function ($assessment) {
                            return [
                                (int) $assessment->id => "Type: {$assessment->assessmentType->name}, Max Score: {$assessment->max_score}"
                            ];
                        })->toArray();
                    })
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
