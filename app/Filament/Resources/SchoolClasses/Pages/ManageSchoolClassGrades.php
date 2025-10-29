<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Models\Grade;
use App\Services\Column;
use App\Models\Assessment;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassGrades extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'grades';

    public $defaultAction = 'manageComponents';

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


                Repeater::make('components')
                    ->hiddenLabel()
                    ->deletable(false)
                    ->orderable(false)
                    ->addable(false)
                    ->collapsible()
                    ->live()
                    ->default(function () {
                        $record = $this->getOwnerRecord();

                        if (! $record || ! $record->gradingComponents) {
                            return [];
                        }

                        return $record->gradingComponents
                            ->map(fn($c) => ['grading_component_id' => $c->id])
                            ->toArray();
                    })
                    ->itemLabel(function (array $state): ?string {
                        $record = $this->getOwnerRecord();

                        if (! $record || ! $record->gradingComponents) {
                            return null;
                        }

                        $component = $record->gradingComponents
                            ->firstWhere('id', $state['grading_component_id'] ?? null);

                        return $component
                            ? "{$component->name} (" . (int) round(floatval($component->weighted_score)) . "%)"
                            : null;
                    })
                    ->afterStateHydrated(function (callable $set, callable $get, $state, $record) {
                        $class = $this->getOwnerRecord(); // SchoolClass model

                        if (! $class) {
                            return;
                        }

                        $components = collect($get('components'));

                        // 1️⃣ Get all grading components of the SchoolClass ordered by sort
                        $gradingComponents = $class->gradingComponents()
                            ->orderBy('sort', 'asc')
                            ->get();

                        if ($gradingComponents->isEmpty()) {
                            return;
                        }

                        // 2️⃣ Build a new, synced list
                        $reordered = $gradingComponents->map(function ($component) use ($components) {
                            // find if this component already exists in the repeater
                            $existing = $components->firstWhere('grading_component_id', $component->id);

                            return [
                                'grading_component_id' => $component->id,
                                'assessment_ids' => $existing['assessment_ids'] ?? [],
                            ];
                        })->values()->toArray();

                        // 3️⃣ Replace repeater state
                        $set('components', $reordered);
                    })
                    ->schema([
                        Hidden::make('grading_component_id')
                            ->required(),

                        CheckboxList::make('assessment_ids')
                            ->hiddenLabel()
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
                            ->columns(2)
                            ->bulkToggleable()
                            ->searchable()
                            ->required()
                            ->descriptions(function ($record, $get) {
                                // Get available assessments for descriptions
                                $allSelected = collect($get('../../components'))
                                    ->pluck('assessment_ids')
                                    ->flatten()
                                    ->filter()
                                    ->all();

                                $currentSelected = collect($get('assessment_ids'))->all();
                                $selectedInSiblings = array_diff($allSelected, $currentSelected);

                                return Assessment::query()
                                    ->whereNotIn('id', $selectedInSiblings)
                                    ->with('assessmentType')
                                    ->get()
                                    ->mapWithKeys(function ($assessment) {
                                        return [
                                            (int) $assessment->id => "{$assessment->assessmentType->name} ({$assessment->max_score})"
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->live()

                    ])
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
                        ->searchable(),

                    Column::boolean(
                        name: 'status',
                        trueLabel: 'Fully Assigned',
                        falseLabel: 'Needs Assignment',
                        trueDesc: 'All grading components already have assigned assessments.',
                        falseDesc: 'There are grading components without assigned assessments.'
                    )
            ])
            ->paginated(false)
            ->actionsAlignment('start')
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manageComponents')
                ->label('Manage Components')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->modalWidth(Width::Large)
                ->model(fn () => $this->getOwnerRecord()) // ✅ bind to current SchoolClass model
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
                    Repeater::make('gradingComponents')
                        ->relationship('gradingComponents') // ✅ repeater tied to hasMany relation
                        ->hiddenLabel()
                        ->collapsible()
                        ->orderable()
                        ->collapsed(fn () => $this->getOwnerRecord()?->gradingComponents()->exists())
                        ->minItems(1)
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
                        ]),
                ])
                ->action(function ($data, $record) {
                    // 🎯 No need to handle saving manually — Filament will sync the relationship automatically
                    Notification::make()
                        ->title('Grading components saved successfully!')
                        ->success()
                        ->send();
                }),
        ];
    }



}
