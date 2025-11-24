<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Models\Grade;
use App\Services\Icon;
use App\Models\Assessment;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Models\GradingComponent;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use App\Models\TransmuteTemplate;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use App\Models\GradeGradingComponent;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\View;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Forms\Components\Repeater\TableColumn;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassGrades extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'grades';

    public $defaultAction = 'settingsAction';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->getOwnerRecord()->gradingComponents()->exists()) {
            $this->defaultAction = null;
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                        fn ($record) => function (string $attribute, $value, $fail) use ($record) {
                            $schoolClassId = $this->getOwnerRecord()->id;

                            $exists = Grade::where('school_class_id', $schoolClassId)
                                ->where('grading_period', $value)
                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                ->exists();

                            if ($exists) {
                                $fail("The grading period '{$value}' already exists for this class.");
                            }
                        },
                    ]),

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
                            ->options(function (callable $get, $record, callable $set, string $operation) {
                                // Handle view mode early
                                if ($operation === 'view') {
                                    $selectedIds = collect($get('assessments'))->filter()->all();
                                    return Assessment::query()
                                        ->whereIn('id', $selectedIds)
                                        ->pluck('name', 'id')
                                        ->mapWithKeys(fn ($name, $id) => [(int) $id => $name])
                                        ->toArray();
                                }

                                // Get current repeater item's selected assessments FIRST
                                $currentItemAssessments = collect($get('assessments'))->filter()->values();

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

                                // Return available assessments
                                return Assessment::query()
                                    ->whereNotIn('id', $allExcludedIds)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })

                    ])
                    ->itemLabel(fn (array $state): ?string =>
                        ($component = GradingComponent::find($state['grading_component_id']))
                            ? $component->label
                            : 'New Component'
                    )
                    ->default(function () {
                        $record = $this->getOwnerRecord();

                        if (! $record || ! $record->gradingComponents) {
                            return [];
                        }

                        return $record->gradingComponents
                            ->map(fn($c) => ['grading_component_id' => $c->id])
                            ->toArray();
                    })
                    ->afterStateHydrated(function (callable $set, callable $get, $state, $record) {
                        $class = $this->getOwnerRecord(); // parent (SchoolClass)
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

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('grading_period')
            ->searchable(false)
            ->columns([
                TextColumn::make('grading_period')
                        ->label('Grading Period')
                        ->badge()
                        ->color('warning')
                        ->sortable()
                        ->searchable()
                        ->toggleable(false),
            ])
            ->paginated(false)
            ->actionsAlignment('start')
            ->headerActions([
                CreateAction::make()->modalWidth(Width::TwoExtraLarge),
                // TODO:: Summary action modal
            ])
            ->recordActions([
                static::viewGrades($this->getOwnerRecord()),
                ViewAction::make()->modalWidth(Width::TwoExtraLarge),
                EditAction::make()->modalWidth(Width::TwoExtraLarge),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('settingsAction')
                ->label('Settings')
                ->model(fn () => $this->getOwnerRecord()) // bind to owner SchoolClass model
                ->icon(Icon::settings())
                ->color('gray')
                ->modalWidth(Width::TwoExtraLarge)
                ->fillForm(fn ($record) => [
                    'gradingComponents' => $record->gradingComponents()
                        ->get(['id', 'name', 'weighted_score'])
                        ->map(fn ($item) => [
                            'id' => $item->id,
                            'name' => $item->name,
                            'weighted_score' => $item->weighted_score,
                        ])
                        ->toArray(),
                ])
                ->form([
                    Tabs::make('Tabs')
                        ->tabs([
                            static::formTabGradingComponents($this->getOwnerRecord()),
                            static::formTabTransmutationTable($this->getOwnerRecord()),
                        ])
                ])
                ->action(function ($data, $record) {
                    // No need to handle saving manually — Filament will sync the relationship automatically
                    Notification::make()
                        ->title('Saved')
                        ->success()
                        ->send();
                }),
        ];
    }

    private static function formTabTransmutationTable($ownerRecord)
    {
        return Tab::make('Transmutation Table')
                ->icon(Icon::transmutations())
                ->schema([
                    Repeater::make('gradeTransmutations')
                    ->relationship('gradeTransmutations')
                    ->hiddenLabel()
                    ->collapsible()
                    ->minItems(1)
                    ->collapsed($ownerRecord?->gradeTransmutations()->exists())
                    ->itemLabel(fn (array $state): ?string =>
                        isset($state['initial_min'], $state['initial_max'], $state['transmuted_grade'])
                            ? number_format((float) $state['initial_min'], 2, '.', '') . "-" . number_format((float) $state['initial_max'], 2, '.', '') . " → {$state['transmuted_grade']}"
                            : 'New Transmutation Range'
                    )
                    ->compact()
                    ->table([
                        TableColumn::make('Initial Min'),
                        TableColumn::make('Initial Max'),
                        TableColumn::make('Grade'),
                    ])
                    ->schema([
                        ...static::rangesField(),
                    ])
                    ->addActionLabel('Add range')
                    ->aboveContent([
                        Action::make('copyTransmuteTemplate')
                            ->label('Copy from Template')
                            ->icon('heroicon-o-document-duplicate')
                            ->modalWidth(Width::Large)
                            ->form([
                                Select::make('template_id')
                                    ->label('Select Template')
                                    ->options(TransmuteTemplate::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Choose a template')
                            ])
                            ->action(function (array $data, Repeater $component) {
                                $template = TransmuteTemplate::find($data['template_id']);

                                if (!$template) {
                                    Notification::make()
                                        ->title('Template not found')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                // Get existing repeater items
                                $existingData = $component->getState() ?? [];

                                // Build the transmutation data from template
                                $templateData = $template->transmuteTemplateRanges->map(function ($range) {
                                    return [
                                        'initial_min' => $range->initial_min,
                                        'initial_max' => $range->initial_max,
                                        'transmuted_grade' => $range->transmuted_grade,
                                    ];
                                })->toArray();

                                // Merge existing items with template data (append template items)
                                $mergedData = array_merge($existingData, $templateData);

                                // Sort by initial_max - ASCENDING
                                // usort($mergedData, function ($a, $b) {
                                //     return ($a['initial_max'] ?? '') <=> ($b['initial_max'] ?? '');
                                // });

                                // Sort by initial_max - DESCENDING
                                usort($mergedData, function ($a, $b) {
                                    return ($b['initial_max'] ?? '') <=> ($a['initial_max'] ?? '');
                                });

                                // Set the combined data to the repeater
                                $component->state($mergedData);

                                Notification::make()
                                    ->title('Template items added successfully')
                                    ->body('Please review for any duplicates before saving.')
                                    ->success()
                                    ->send();
                            })
                            ->modalSubmitActionLabel('Copy & Paste'),

                        Action::make('deleteAll')
                            ->label('Delete All')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->modalHeading('Delete all transmutation ranges?')
                            ->modalDescription('Are you sure you want to delete all transmutation ranges? This action cannot be undone.')
                            ->action(function (Repeater $component) {
                                // Clear all items
                                $component->state([]);

                                Notification::make()
                                    ->title('All items deleted')
                                    ->success()
                                    ->send();
                            })
                    ])
                ]); // end schema
    }

    public static function rangesField(bool $isRepeater = true)
    {
        return [
            TextInput::make('initial_min')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(100)
                ->step(0.01)
                ->placeholder('e.g., 0.00')
                ->live(onBlur: true)
                ->rules([
                    fn ($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                        $maxValue = $get('initial_max');
                        if ($maxValue !== null && $value > $maxValue) {
                            $fail('Minimum must be less than or equal to maximum.');
                        }
                    }
                ])
                ->when($isRepeater, fn ($field) => $field->distinct())
                ->when(!$isRepeater, fn ($field) => $field->scopedUnique())
                ->columnSpan(1),

            TextInput::make('initial_max')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(100)
                ->step(0.01)
                ->placeholder('e.g., 99.99')
                ->live(onBlur: true)
                ->rules([
                    fn ($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                        $minValue = $get('initial_min');
                        if ($minValue !== null && $value < $minValue) {
                            $fail('Maximum must be greater than or equal to minimum.');
                        }
                    }
                ])
                ->when($isRepeater, fn ($field) => $field->distinct())
                ->when(!$isRepeater, fn ($field) => $field->scopedUnique())
                ->columnSpan(1),

            TextInput::make('transmuted_grade')
                ->placeholder('e.g., 99, 1.00, A+')
                ->required()
                ->maxLength(10)
                ->when($isRepeater, fn ($field) => $field->distinct())
                ->when(!$isRepeater, fn ($field) => $field->scopedUnique())
                ->columnSpan(1),

        ];
    }

    private static function formTabGradingComponents($ownerRecord)
    {
        return Tab::make('Grading Components')
                ->icon(Icon::gradingComponents())
                ->schema([
                    Repeater::make('gradingComponents')
                        ->relationship('gradingComponents') // repeater tied to hasMany relation
                        ->hiddenLabel()
                        ->collapsible()
                        ->orderable()
                        ->minItems(1)
                        ->collapsed($ownerRecord?->gradingComponents()->exists())
                        ->afterStateHydrated(function ($component, $state) {
                            // When editing: if no data is loaded, create 1 empty row.
                            if (blank($state)) {
                                $component->state([[]]);
                            }
                        })
                        ->itemLabel(fn (array $state): ?string =>
                            isset($state['name'], $state['weighted_score'])
                                ? "{$state['name']} ({$state['weighted_score']}%)"
                                : ($state['name'] ?? 'New Component')
                        )
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('name')
                                        ->placeholder('Enter component name...')
                                        ->helperText('You can type or pick from suggestions.')
                                        ->required()
                                        ->maxLength(255)
                                        ->datalist([
                                            'Written Works',
                                            'Performance Tasks',
                                            'Quarterly Assessment',
                                            'Quiz',
                                            'Exam',
                                            'Oral',
                                        ])
                                        ->columnSpan(2),

                                    TextInput::make('weighted_score')
                                        ->label('Weighted Score')
                                        ->helperText('Value between 1-100')
                                        ->numeric()
                                        ->required()
                                        ->minValue(1)
                                        ->maxValue(100)
                                        ->step(0.01)
                                        ->suffix('%')
                                        ->columnSpan(1),
                                ]),
                        ])
                        ->rules([
                            fn ($get) => function (string $attribute, $value, $fail) use ($get) {
                                $total = collect($get('gradingComponents'))->sum('weighted_score');
                                if ($total != 100) {
                                    $fail("The total weighted score of all components must equal 100%. Current total: {$total}%");
                                }
                            },
                        ])
                        ->deleteAction(
                            fn (Action $action) => $action->requiresConfirmation()
                        )
                        ->addActionLabel('Add grading component')
                    ]);
    }

    private static function viewGrades($getOwnerRecord)
    {
        return
            Action::make('grades')
                ->icon('heroicon-o-list-bullet')
                ->color('info')
                ->modalHeading(fn ($record) => $record->grading_period)
                ->modalDescription(new HtmlString(
                    '💡 Note: This table adjusts automatically. When there are many columns, a horizontal scrollbar will appear. Scroll left/right or hold <kbd style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 1px 4px; border-radius: 3px; font-size: 0.6rem;">Shift</kbd> to view all columns.'
                ))
                ->form(function () use ($getOwnerRecord) {
                    return [
                        Select::make('student_filter')
                            ->label('Filter by Student')
                            ->placeholder('All Students')
                            ->options(function () use ($getOwnerRecord) {
                                return $getOwnerRecord->students()
                                    ->orderBy('last_name')
                                    ->orderBy('first_name')
                                    ->get()
                                    ->pluck('full_name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->multiple()
                            ->extraAttributes([
                                'style' => 'position: relative; z-index: 50;',
                            ]),

                            View::make('filament.tables.grades')
                                ->viewData(function ($get, $record) use ($getOwnerRecord) {
                                    // Get the selected student filter
                                    $studentFilter = $get('student_filter');

                                    // Process data
                                    $schoolClass = $getOwnerRecord;
                                    $gradeGradingComponents = $record->orderedGradeGradingComponents;

                                    $groupedAssessments = $record->orderedGradeGradingComponents
                                        ->load(['gradingComponent', 'assessments'])
                                        ->groupBy(fn($ggc) => $ggc->gradingComponent?->label)
                                        ->map(fn($group) => $group->flatMap->assessments);

                                    // Calculate total columns
                                    $totalAssessmentColumns = $groupedAssessments->sum(fn($assessments) => $assessments->count() + 3);
                                    $totalColumns = $totalAssessmentColumns + 2;

                                    // Filter students based on selection
                                    $studentsQuery = $schoolClass->students();

                                    if (!empty($studentFilter)) {
                                        $studentsQuery->whereIn('students.id', $studentFilter);
                                    }

                                    $students = $studentsQuery->get()->groupBy('gender');

                                    $percentageScore = 100;

                                    // Return all data to the view
                                    return [
                                        'record' => $record,
                                        'schoolClass' => $schoolClass,
                                        'gradeGradingComponents' => $gradeGradingComponents,
                                        'groupedAssessments' => $groupedAssessments,
                                        'totalAssessmentColumns' => $totalAssessmentColumns,
                                        'totalColumns' => $totalColumns,
                                        'students' => $students,
                                        'percentageScore' => $percentageScore,
                                        'studentFilter' => $studentFilter,
                                    ];
                                }),
                        ];
                    })
                    ->modalWidth(Width::SevenExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalAutofocus(false);

    }

}
