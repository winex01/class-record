<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Models\Lesson;
use App\Services\Field;
use Filament\Tables\Table;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\Column;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Relaticle\Flowforge\Concerns\BaseBoard;
use Relaticle\Flowforge\Contracts\HasBoard;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassLessons extends ManageRelatedRecords implements Hasboard
{
    use BaseBoard;
    protected string $view = 'flowforge::filament.pages.board-page';
    protected static string $resource = SchoolClassResource::class;
    protected static string $relationship = 'lessons';
    protected static ?string $model = Lesson::class;

    public function board(Board $board): Board
    {
        return $board
            ->query($this->getOwnerRecord()->lessons()->getQuery())
            ->recordTitleAttribute('title')
            ->columnIdentifier('status')
            ->positionIdentifier('position')
            ->columns([
                Column::make('topics')->color('info'),
                Column::make('in_progress')->label('In Progress')->color('warning'),
                Column::make('Completed')->color('primary'),
            ])
            ->filters([
                // NOTE:: this board is just a hacky way solution i did because there is no ManageRelatedRecords example
                // available in the plugin https://relaticle.github.io/flowforge/, i notice as long as i have
                // at least 1 filter which is not hidden the ->searchable(['title']) below will work no need to
                // manually add the logic in ->query() using $this->tableSearch.
                SelectFilter::make('status')
                ->options([
                    'topics' => 'Topics',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                ]),
            ])
            ->searchable(['title'])
            ->columnActions([
                CreateAction::make()
                    ->hiddenLabel()
                    ->button()
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->modalWidth(Width::Large)
                    ->form($this->getForm())
                    ->model(static::$model)

            ])
            ->cardActions([
                ViewAction::make()->modalWidth(Width::Large)->form($this->getForm()),
                EditAction::make()->modalWidth(Width::Large)->form($this->getForm()),
                DeleteAction::make(),
            ]);
    }

    protected function getForm()
    {
        return [
            Hidden::make('school_class_id')->default($this->getOwnerRecord()->id),

            Hidden::make('status')
            ->default(function ($livewire) {
                // Access the column from mountedActions
                if (!empty($livewire->mountedActions)) {
                    $firstAction = $livewire->mountedActions[0];
                    if (isset($firstAction['arguments']['column'])) {
                        $column = $firstAction['arguments']['column'];

                        $statusMap = [
                            'topics' => 'topics',
                            'in_progress' => 'in_progress',
                            'Completed' => 'completed'
                        ];

                        return $statusMap[$column] ?? 'topics';
                    }
                }

                return 'topics'; // fallback
            }),

            TextInput::make('title')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->nullable()
                ->rows(3)
                ->columnSpanFull(),

            Field::tags('tags'),

            Field::date('completion_date'),

            // TODO:: column span and maybe use toggle
            Repeater::make('checklist')
                ->nullable()
                ->schema([
                    TextInput::make('item')
                        ->required()
                        ->placeholder('Enter checklist item'),
                    Checkbox::make('completed')
                        ->default(false),
                ])
                ->columns(2)
                ->itemLabel(fn (array $state): ?string => $state['item'] ?? null),
        ];
    }
}
