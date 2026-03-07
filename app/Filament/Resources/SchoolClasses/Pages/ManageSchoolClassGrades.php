<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use App\Models\SchoolClass;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Filament\Fields\Select;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use App\Models\TransmuteTemplate;
use Filament\Support\Enums\Width;
use App\Filament\Fields\TextInput;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TextColumn;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use App\Filament\Fields\NumericInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Support\Enums\Alignment;
use App\Models\GradeComponentTemplate;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use Filament\Forms\Components\Repeater\TableColumn;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Forms\SchoolClassGradeForm;
use App\Filament\Resources\TransmuteTemplates\TransmuteTemplateResource;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassGradeActions;
use App\Filament\Resources\GradeComponentTemplates\GradeComponentTemplateResource;
use App\Filament\Resources\GradeComponentTemplates\Forms\GradeComponentTemplateForm;

class ManageSchoolClassGrades extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;
    protected static string $relationship = 'grades';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (!$this->getOwnerRecord()->gradingComponents()->exists()) {
            $this->dispatch('mountGradingSettings');
        }
    }

    #[On('mountGradingSettings')]
    public function openGradingSettings(): void
    {
        $this->mountTableAction('gradingSettingsAction');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(SchoolClassGradeForm::schema($this->getOwnerRecord()));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('grading_period')
            ->defaultSort('grading_period', 'asc')
            ->columns([
                TextColumn::make('grading_period')
                    ->label('Grading Period')
                    ->color('primary')
                    ->size(TextSize::Large)
                    ->searchable(false)
            ])
            ->paginated(false)
            ->actionsAlignment('start')
            ->recordActions([
                SchoolClassGradeActions::viewGradesAction($this->getOwnerRecord()),
                ViewAction::make()->modalWidth(Width::TwoExtraLarge),
                EditAction::make()->modalWidth(Width::TwoExtraLarge),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()->label('New Grade')->modalWidth(Width::TwoExtraLarge),
                $this->getGradingSettingsAction(),
                DeleteBulkAction::make(),
            ])
            ->recordAction('grades');
    }

    // TODO:: here!!, TBD:: create own file?
    public function getGradingSettingsAction(): Action
    {
        return Action::make('gradingSettingsAction')
            ->model(SchoolClass::class)
            ->disabledForm(fn () => !$this->getOwnerRecord()->active)
            ->label('Grading Settings')
            ->icon(Heroicon::OutlinedCog8Tooth)
            ->color('pink')
            ->modalWidth(Width::TwoExtraLarge)
            ->fillForm(function () {
                return [
                    'gradingComponents' => $this->getOwnerRecord()->gradingComponents()
                        ->get(['id', 'name', 'weighted_score'])
                        ->map(fn ($item) => [
                            'id' => $item->id,
                            'name' => $item->name,
                            'weighted_score' => $item->weighted_score,
                        ])
                        ->toArray(),

                    'gradeTransmutations' => $this->getOwnerRecord()->gradeTransmutations()
                        ->get(['id', 'initial_min', 'initial_max', 'transmuted_grade'])
                        ->map(fn ($item) => [
                            'id' => $item->id,
                            'initial_min' => $item->initial_min,
                            'initial_max' => $item->initial_max,
                            'transmuted_grade' => $item->transmuted_grade,
                        ])
                        ->toArray(),
                ];
            })
            ->form(function () {
                return [
                    Tabs::make('Tabs')->tabs([
                        $this->formTabGradingComponents(),
                        $this->formTabTransmutationTable(),
                    ])
                ];
            })
            ->action(function (array $data, $livewire) {
                $record = $livewire->record;

                $record->gradingComponents()->delete();
                // working and no problem
                foreach ($data['gradingComponents'] as $component) {
                    $record->gradingComponents()->create([
                        'name' => $component['name'],
                        'weighted_score' => $component['weighted_score'],
                    ]);
                }

                $record->gradeTransmutations()->delete();
                foreach ($data['gradeTransmutations'] as $component) {
                    $record->gradeTransmutations()->create([
                        'initial_min' => $component['initial_min'],
                        'initial_max' => $component['initial_max'],
                        'transmuted_grade' => $component['transmuted_grade'],
                    ]);
                }

                Notification::make()->title('Saved')->success()->send();
            })
            ->modalSubmitActionLabel(function () {
                return $this->getOwnerRecord()->gradingComponents()->exists() ? 'Save Changes' : 'Save';
            });
    }

    private function formTabTransmutationTable()
    {
        return Tab::make('Transmutation Table')
                ->icon(TransmuteTemplateResource::getNavigationIcon())
                ->schema([
                    Repeater::make('gradeTransmutations')
                        ->hiddenLabel()
                        ->compact()
                        ->orderable(false)
                        ->table([
                            TableColumn::make('Initial Min'),
                            TableColumn::make('Initial Max'),
                            TableColumn::make('Grade'),
                        ])
                        ->schema(array_map(fn ($field) => $field->distinct(), static::rangesField()))
                        ->afterStateHydrated(function (Repeater $component, $state) {
                            if (is_array($state) && count($state) > 0) {
                                // Sort by initial_max - DESCENDING
                                usort($state, function ($a, $b) {
                                    return ($b['initial_max'] ?? 0) <=> ($a['initial_max'] ?? 0);
                                });

                                $component->state($state);
                            }
                        })
                        ->addActionLabel('Add range')
                        ->aboveContent([
                            Action::make('copyTransmuteTemplate')
                                ->label('Copy from Templates')
                                ->icon(icon: 'heroicon-o-document-duplicate')
                                ->modalWidth(Width::Large)
                                ->modalHeading('Transmute Templates')
                                ->form([
                                    Select::make('template_id')
                                        ->label('Select Template')
                                        ->options(TransmuteTemplate::query()->pluck('name', 'id'))
                                        ->default(fn () => TransmuteTemplate::query()->first()?->id)
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

                                    // Build the transmutation data from template (overwrites everything)
                                    $templateData = $template->transmuteTemplateRanges
                                        ->map(function ($range) {
                                            return [
                                                'initial_min' => $range->initial_min,
                                                'initial_max' => $range->initial_max,
                                                'transmuted_grade' => $range->transmuted_grade,
                                            ];
                                        })
                                        ->toArray();

                                    // Sort by initial_max - DESCENDING
                                    usort($templateData, function ($a, $b) {
                                        return ($b['initial_max'] ?? 0) <=> ($a['initial_max'] ?? 0);
                                    });

                                    // Set the template data directly (no merging)
                                    $component->state($templateData);
                                })
                                ->modalSubmitActionLabel('Copy & Paste'),

                            Action::make('deleteAll')
                                ->label('Delete All')
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->modalHeading('Delete All')
                                ->modalDescription('Are you sure you want to delete all transmutation ranges?')
                                ->modalFooterActionsAlignment(Alignment::Center)
                                ->action(function (Repeater $component) {
                                    // Clear all items
                                    $component->state([]);
                                })
                        ])
                        ->deleteAction(function (Action $action) {
                            return
                                $action->modalFooterActionsAlignment(Alignment::Center)
                                ->requiresConfirmation(
                                    function (array $arguments, Repeater $component): bool {
                                        return isset($component->getRawItemState($arguments['item'])['id']);
                                    }
                                );
                        })
                ]); // end schema
    }

    private function formTabGradingComponents()
    {
        return Tab::make('Grading Components')
                ->icon(GradeComponentTemplateResource::getNavigationIcon())
                ->schema([
                    Repeater::make('gradingComponents')
                        ->hiddenLabel()
                        ->collapsible()
                        ->orderable('sort')
                        ->minItems(1)
                        ->collapsed($this->getOwnerRecord()?->gradingComponents()->exists())
                        ->schema(static::getComponentFields())
                        ->afterStateHydrated(function ($component, $state) {
                            if (blank($state)) {
                                $component->state([[]]);
                                return;
                            }

                            // Fix orderable bug - use UUID keys
                            $updatedItems = [];
                            foreach ($state as $item) {
                                if (is_array($item)) {
                                    $updatedItems[(string) Str::uuid()] = $item;
                                }
                            }

                            $component->state($updatedItems);
                        })
                        ->itemLabel(fn (array $state): ?string =>
                            isset($state['name'], $state['weighted_score'])
                                ? "{$state['name']} ({$state['weighted_score']}%)"
                                : ($state['name'] ?? 'New Component')
                        )
                        ->rules([
                            fn ($get) => function (string $attribute, $value, $fail) use ($get) {
                                $total = collect($get('gradingComponents'))->sum('weighted_score');
                                if ($total != 100) {
                                    $fail("The total weighted score of all components must equal 100%. Current total: {$total}%");
                                }
                            },
                        ])
                        ->addActionLabel('Add grading component')
                        ->aboveContent([
                            Action::make('copyGradeComponentTemplate')
                                ->label('Copy from Templates')
                                ->icon(icon: 'heroicon-o-document-duplicate')
                                ->modalWidth(Width::Large)
                                ->modalHeading('Grade Component Templates')
                                ->form([
                                    Select::make('template_id')
                                        ->label('Select Template')
                                        ->options(GradeComponentTemplate::query()->orderBy('name')->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->placeholder('Choose a template')
                                        ->suffixActions([
                                            Action::make('createGradeComponentTemplate')
                                                ->icon('heroicon-m-plus')
                                                ->modalWidth(Width::ExtraLarge)
                                                ->modalHeading('Create Grade Component Template')
                                                ->model(GradeComponentTemplate::class) // i added this
                                                ->form(GradeComponentTemplateForm::schema())
                                                ->action(function (array $data, Select $component) {
                                                    // Create the new template
                                                    $template = GradeComponentTemplate::create($data);

                                                    // Update the select options and set the newly created template as selected
                                                    $component->options(
                                                        GradeComponentTemplate::query()->pluck('name', 'id')
                                                    );

                                                    $component->state($template->id);

                                                    Notification::make()
                                                        ->title('Template created successfully')
                                                        ->success()
                                                        ->send();
                                                })
                                                ->modalSubmitActionLabel('Create'),
                                        ])
                                ])
                                ->action(function (array $data, Repeater $component) {
                                    $template = GradeComponentTemplate::find($data['template_id']);

                                    if (!$template) {
                                        Notification::make()
                                            ->title('Template not found')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    // Transform template components to repeater format (overwrites everything)
                                    $templateData = collect($template->components)
                                        ->map(fn($item) => [
                                            'name' => data_get($item, 'name', ''),
                                            'weighted_score' => data_get($item, 'weighted_score', 0),
                                        ])
                                        ->toArray();

                                    $component->state($templateData);
                                })
                                ->modalSubmitActionLabel('Copy & Paste'),

                            Action::make('deleteAll')
                                ->label('Delete All')
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->modalHeading('Delete All')
                                ->modalDescription('Are you sure you want to delete all components?')
                                ->modalFooterActionsAlignment(Alignment::Center)
                                ->action(function (Repeater $component) {
                                    // Clear all items
                                    $component->state([]);
                                })
                        ])
                        ->deleteAction(function (Action $action) {
                            return
                                $action->modalFooterActionsAlignment(Alignment::Center)
                                ->requiresConfirmation(
                                    function (array $arguments, Repeater $component): bool {
                                        return isset($component->getRawItemState($arguments['item'])['id']);
                                    }
                                );
                        })
                ]);
    }

    public static function getComponentFields()
    {
        return [
            Grid::make(3)
            ->schema([
                TextInput::make('name')
                    ->placeholder('Enter component name...')
                    ->helperText('You can type or pick from suggestions.')
                    ->required()
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
        ];
    }

    public static function rangesField()
    {
        // NOTE:: the distinct() and scopeUniqued() i place it where it will be used not here to make it reusable both in normal forms and in repeater
        return [
            NumericInput::make('initial_min')
                ->required()
                ->minValue(0)
                ->maxValue(100)
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
                ->columnSpan(1),

            NumericInput::make('initial_max')
                ->required()
                ->minValue(0)
                ->maxValue(100)
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
                ->columnSpan(1),

            TextInput::make('transmuted_grade')
                ->placeholder('e.g., 99, 1.00, A+')
                ->required()
                ->maxLength(10)
                ->columnSpan(1),

        ];
    }
}
