<?php

namespace App\Filament\Resources\SchoolClasses\Forms;

use App\Models\Grade;
use App\Models\Assessment;
use App\Models\SchoolClass;
use App\Models\GradingComponent;
use App\Filament\Fields\TextInput;
use App\Models\GradeGradingComponent;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\CheckboxList;

class SchoolClassGradeForm
{
    public static function schema(SchoolClass $ownerRecord)
    {
        return [
            TextInput::make('grading_period')
            ->placeholder('Enter grading period...')
            ->helperText('You can type or pick from suggestions.')
            ->required()
            ->maxLength(255)
            ->datalist([
                '1st Quarter',
                '2nd Quarter',
                '3rd Quarter',
                '4th Quarter',
                'Midterm',
                'Finals',
            ])
            ->rules([
                fn ($record) => function (string $attribute, $value, $fail) use ($record, $ownerRecord) {
                    $schoolClassId = $ownerRecord->id;

                    $exists = Grade::where('school_class_id', $schoolClassId)
                        ->where('grading_period', $value)
                        ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                        ->exists();

                    if ($exists) {
                        $fail("The grading period '{$value}' already exists for this class.");
                    }
                },
            ]),

            static::gradeGradingComponentsRepeaterField($ownerRecord),
        ];
    }

    public static function gradeGradingComponentsRepeaterField(SchoolClass $ownerRecord)
    {
        return
            Repeater::make('gradeGradingComponents')
            ->relationship('gradeGradingComponents')
            ->hiddenLabel()
            ->schema([
                Hidden::make('grading_component_id')
                    ->distinct()
                    ->required(),

                CheckboxList::make('assessments')
                    ->hiddenLabel()
                    ->relationship('assessments', 'name')
                    ->searchPrompt('Start typing to search assessment...')
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(2)
                    ->required()
                    ->distinct()
                    ->minItems(1)
                    ->live()
                    ->options(function (callable $get, $livewire, callable $set, string $operation) {
                        // Handle view mode early
                        if ($operation === 'view') {
                            $selectedIds = collect($get('assessments'))->filter()->all();
                            return Assessment::query()
                                ->whereIn('id', $selectedIds)
                                ->pluck('name', 'id')
                                ->mapWithKeys(fn ($name, $id) => [(int) $id => $name])
                                ->toArray();
                        }

                        // Get all repeater state
                        $allItems = collect($get('../../gradeGradingComponents') ?? []);

                        // Collect all selected assessment IDs from other repeater items
                        $selectedInOtherItems = $allItems
                            ->reject(fn ($item) => $item === $get())
                            ->pluck('assessments')
                            ->flatten()
                            ->filter()
                            ->unique()
                            ->values();

                        // Get the current Grade ID from the form data (only available in edit)
                        $currentGradeId = $get('../../id');

                        // Get assessments from OTHER GradeGradingComponent records ONLY
                        $otherDatabaseAssessments = GradeGradingComponent::query()
                            ->when($operation === 'edit' && $currentGradeId, function ($query) use ($currentGradeId) {
                                // In EDIT: Exclude GradeGradingComponents that belong to the current Grade
                                return $query->where('grade_id', '!=', $currentGradeId);
                            }, function ($query) use ($operation) {
                                // In CREATE: Exclude all existing assignments
                                return $query; // This will exclude all GradeGradingComponent assessments
                            })
                            ->whereHas('assessments')
                            ->get()
                            ->pluck('assessments.*.id')
                            ->flatten()
                            ->unique()
                            ->values();

                        // Combine exclusions (other repeater items + other database records)
                        $allExcludedIds = $selectedInOtherItems
                            ->merge($otherDatabaseAssessments)
                            ->unique()
                            ->values();

                        // Get school_class_id from the record
                        $schoolClassId = $livewire->record?->id ?? null;

                        // Return available assessments
                        return Assessment::query()
                            ->whereNotIn('id', $allExcludedIds)
                            ->when($schoolClassId, fn($query) => $query->where('school_class_id', $schoolClassId))
                            ->pluck('name', 'id')
                            ->toArray();
                    })

            ])
            ->itemLabel(fn (array $state): ?string =>
                ($component = GradingComponent::find($state['grading_component_id']))
                    ? $component->label
                    : 'New Component'
            )
            ->default(function () use ($ownerRecord) {
                $record = $ownerRecord;

                if (! $record || ! $record->gradingComponents) {
                    return [];
                }

                return $record->gradingComponents
                    ->map(fn($c) => ['grading_component_id' => $c->id])
                    ->toArray();
            })
            ->afterStateHydrated(function (callable $set, callable $get, $state, $record) use ($ownerRecord) {
                $class = $ownerRecord; // parent (SchoolClass)
                if (! $class) {
                    return;
                }

                // 🔹 Get all grading components ordered by sort
                $gradingComponents = $class->gradingComponents()
                    ->orderBy('sort', 'asc')
                    ->get();

                if ($gradingComponents->isEmpty()) {
                    return;
                }

                $items = collect($state);

                // 🔹 Rebuild or reorder repeater items
                $reordered = $gradingComponents->map(function ($component) use ($items) {
                    $existing = $items->firstWhere('grading_component_id', $component->id);

                    return [
                        'grading_component_id' => $component->id,
                        // preserve other subfields if they exist
                        ...($existing ?? []),
                    ];
                })->values()->toArray();

                // 🔹 Apply reordered state back to the repeater
                $set('gradeGradingComponents', $reordered);
            })
            ->collapsible()
            ->deletable(false)
            ->addable(false)
            ->minItems(1)
            ->validationMessages([
                'min' => 'Please configure grading components by clicking the Settings button above the New Grade button.',
            ]);
    }
}
