<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Models\Grade;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Actions\Action;
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
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassGrades extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'grades';

    public $defaultAction = 'gradingComponents';

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

                Field::tags('tags'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('grading_period')
            ->columns([
                TextColumn::make('grading_period')
                    ->searchable()
                    ->sortable(),

                Column::tags('tags'),
            ])
            ->headerActions([
                CreateAction::make()->modalWidth(Width::TwoExtraLarge)
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
            Action::make('gradingComponents')
                ->label('Grading Components')
                ->icon('heroicon-o-cog-6-tooth')
                ->modalWidth(Width::ExtraLarge)
                ->color('gray')
                ->form($this->gradingComponentForm())
                ->mountUsing(function ($form, $livewire) {
                    $schoolClass = $this->getOwnerRecord();

                    // Fetch related grading components
                    $components = $schoolClass->gradingComponents()
                        ->get(['name', 'weighted_score'])
                        ->toArray();

                    // Fill the form
                    $form->fill([
                        'components' => !empty($components)
                            ? $components
                            : [['name' => '', 'weighted_score' => null]], // default one item
                    ]);
                })
                ->action(function (array $data): void {
                    $schoolClass = $this->getOwnerRecord();

                    // Clear existing grading components for this class
                    $schoolClass->gradingComponents()->delete();

                    // Reinsert all components from form
                    foreach ($data['components'] as $component) {
                        $schoolClass->gradingComponents()->create([
                            'name' => $component['name'],
                            'weighted_score' => $component['weighted_score'],
                        ]);
                    }

                    Notification::make()
                        ->title('Grading components saved successfully!')
                        ->success()
                        ->send();
                })

        ];
    }

    public function gradingComponentForm()
    {
        return [
            Repeater::make('components')
                ->hiddenLabel()
                ->live()
                ->collapsible()
                ->minItems(1) // validation 1 item
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
                                ->distinct()
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
                    fn ($get)=> function (string $attribute, $value, $fail) use ($get) {
                        // $value = the current components repeater array
                        $total = collect($get('components'))->sum('weighted_score');

                        if ($total != 100) {
                            $fail("The total weighted score of all components must equal 100%. Current total: {$total}%");
                        }
                    },
                ])
        ];
    }
}
