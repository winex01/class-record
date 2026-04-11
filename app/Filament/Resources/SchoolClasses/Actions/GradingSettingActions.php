<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use App\Models\SchoolClass;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use App\Filament\Fields\Select;
use App\Models\TransmuteTemplate;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Tabs;
use App\Models\GradeComponentTemplate;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use App\Filament\Actions\DeleteAllAction;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Actions\RemoveItemAction;
use Filament\Forms\Components\Repeater\TableColumn;
use App\Filament\Resources\TransmuteTemplates\TransmuteTemplateResource;
use App\Filament\Resources\TransmuteTemplates\Schemas\TransmuteTemplateRangeForm;
use App\Filament\Resources\GradeComponentTemplates\GradeComponentTemplateResource;
use App\Filament\Resources\GradeComponentTemplates\Schemas\GradeComponentTemplateForm;

class GradingSettingActions
{
    public static function action(SchoolClass $ownerRecord)
    {
        return
            Action::make('gradingSettingsAction')
            ->model(SchoolClass::class)
            ->label('Grading Settings')
            ->disabledForm(fn () => !$ownerRecord->active)
            ->icon(Heroicon::OutlinedCog8Tooth)
            ->color('pink')
            ->modalWidth(Width::TwoExtraLarge)
            ->fillForm(function () use ($ownerRecord) {
                return [
                    'gradingComponents' => $ownerRecord->gradingComponents()
                        ->get(['id', 'name', 'weighted_score'])
                        ->map(fn ($item) => [
                            'id' => $item->id,
                            'name' => $item->name,
                            'weighted_score' => $item->weighted_score,
                        ])
                        ->toArray(),

                    'gradeTransmutations' => $ownerRecord->gradeTransmutations()
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
            ->schema(function () use ($ownerRecord) {
                return [
                    Tabs::make('Tabs')->tabs([
                        Tab::make('Grading Components')
                        ->icon(GradeComponentTemplateResource::getNavigationIcon())
                        ->schema([
                            Repeater::make('gradingComponents')
                                ->hiddenLabel()
                                ->collapsible()
                                ->orderColumn()
                                ->minItems(1)
                                ->collapsed($ownerRecord?->gradingComponents()->exists())
                                ->schema([
                                    Hidden::make('id'),
                                    ...GradeComponentTemplateForm::gradeComponentFields()
                                ])
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
                                    static::copyGradeComponentTemplateAction(),
                                    DeleteAllAction::make()
                                ])
                                ->deleteAction(fn (Action $action) => RemoveItemAction::confirmIfSaved($action))
                        ]),

                        Tab::make('Transmutation Table')
                        ->icon(TransmuteTemplateResource::getNavigationIcon())
                        ->schema([
                            Repeater::make('gradeTransmutations')
                                ->hiddenLabel()
                                ->compact()
                                ->orderColumn(false)
                                ->table([
                                    TableColumn::make('Initial Min'),
                                    TableColumn::make('Initial Max'),
                                    TableColumn::make('Grade'),
                                ])
                                ->schema(array_map(fn ($field) => $field->distinct(), TransmuteTemplateRangeForm::getRangeFields()))
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
                                    static::copyTransmuteTemplateAction(),
                                    DeleteAllAction::make(),
                                ])
                                ->deleteAction(fn (Action $action) => RemoveItemAction::confirmIfSaved($action))
                        ]),
                    ])
                ];
            })
            ->action(function (array $data, $livewire) {
                $record = $livewire->record;

                $submittedComponents = collect($data['gradingComponents']);

                $savedIds = $submittedComponents->map(fn ($component, $index) =>
                    $record->gradingComponents()->updateOrCreate(
                        ['id' => $component['id'] ?? null],
                        [
                            'name' => $component['name'],
                            'weighted_score' => $component['weighted_score'],
                            'sort' => $index,
                        ]
                    )->id
                );

                $record->gradingComponents()
                    ->whereNotIn('id', $savedIds)
                    ->delete();

                // Grade Transmutations - delete all and re-insert
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
            ->modalSubmitActionLabel(function () use ($ownerRecord) {
                return $ownerRecord->gradingComponents()->exists() ? 'Save Changes' : 'Save';
            })
            ->modalSubmitAction($ownerRecord?->active ? null : false)
            ->modalCancelAction($ownerRecord?->active ? null : false);
    }

    public static function copyGradeComponentTemplateAction()
    {
        return
            Action::make('copyGradeComponentTemplate')
            ->label('Copy from Templates')
            ->icon(icon: 'heroicon-o-document-duplicate')
            ->modalWidth(Width::Large)
            ->modalHeading('Grade Component Templates')
            ->schema([
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
                        ->schema(GradeComponentTemplateForm::getFields())
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
            ->action(function (array $data, $component) {
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
            ->modalSubmitActionLabel('Copy & Paste');
    }

    public static function copyTransmuteTemplateAction()
    {
        return
            Action::make('copyTransmuteTemplate')
            ->label('Copy from Templates')
            ->icon(icon: 'heroicon-o-document-duplicate')
            ->modalWidth(Width::Large)
            ->modalHeading('Transmute Templates')
            ->schema([
                Select::make('template_id')
                ->label('Select Template')
                ->options(TransmuteTemplate::query()->pluck('name', 'id'))
                ->default(fn () => TransmuteTemplate::query()->first()?->id)
                ->required()
                ->placeholder('Choose a template')
            ])
            ->action(function (array $data, $component) {
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
            ->modalSubmitActionLabel('Copy & Paste');
    }
}
