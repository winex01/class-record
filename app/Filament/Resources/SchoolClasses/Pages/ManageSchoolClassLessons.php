<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Models\Lesson;
use App\Services\Field;
use App\Enums\LessonStatus;
use Filament\Schemas\Schema;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\Column;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\TextSize;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Relaticle\Flowforge\Concerns\BaseBoard;
use Relaticle\Flowforge\Contracts\HasBoard;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Traits\HasSubjectDetailsTrait;
use App\Filament\Resources\MyFiles\MyFileResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Forms\Components\Repeater\TableColumn;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassLessons extends ManageRelatedRecords implements Hasboard
{
    use HasSubjectDetailsTrait;
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
                Column::make(LessonStatus::TOPICS->value)
                    ->label(LessonStatus::TOPICS->getLabel())
                    ->color(LessonStatus::TOPICS->getColor()),
                Column::make(LessonStatus::IN_PROGRESS->value)
                    ->label(LessonStatus::IN_PROGRESS->getLabel())
                    ->color(LessonStatus::IN_PROGRESS->getColor()),
                Column::make(LessonStatus::DONE->value)
                    ->label(LessonStatus::DONE->getLabel())
                    ->color(LessonStatus::DONE->getColor()),
            ])
            ->cardSchema(fn(Schema $schema) => $schema->components($this->getColumns()))
            // ->filters($this->getFilters())
            ->searchable(['title', 'description', 'tags'])
            ->columnActions([
                CreateAction::make()
                    ->hiddenLabel()
                    ->button()
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->form($this->getForm())
                    ->model(static::$model)

            ])
            ->cardActions([
                ViewAction::make()->modalWidth(Width::TwoExtraLarge)->form($this->getForm()),
                EditAction::make()->modalWidth(Width::TwoExtraLarge)->form($this->getForm()),
                DeleteAction::make(),
            ]);
    }

    private function getColumns()
    {
        return [
            TextEntry::make('description')
                ->hiddenLabel()
                ->color('gray')
                ->size(TextSize::Small)
                ->lineClamp(3)
                ->html()
                ->hidden(fn ($record) => empty($record->description))
                ->extraAttributes(['style' => 'margin-top: -25px;']),

            TextEntry::make('tags')
                ->hiddenLabel()
                ->badge()
                ->separator(',')
                ->color('primary')
                ->size(TextSize::Small)
                ->hidden(fn ($record) => empty($record->tags))
                ->extraAttributes(['style' => 'margin-top: -15px;']),

            TextEntry::make('completion_date')
                ->hiddenLabel()
                ->date('M d, Y')
                ->icon('heroicon-o-calendar')
                ->iconColor('primary')
                ->size(TextSize::Small)
                ->hidden(fn ($record) => empty($record->completion_date))
                ->extraAttributes(['style' => 'margin-top: -15px;']),
        ];
    }

    private function getFilters()
    {
        return [
            // NOTE:: this board is just a hacky way solution i did because there is no ManageRelatedRecords example
            // available in the plugin https://relaticle.github.io/flowforge/, i notice as long as i have
            // at least 1 filter which is not hidden then the search bar will worked!
            SelectFilter::make('tags')
            ->label('Tags')
            ->multiple()
            ->options(function () {
                return $this->getOwnerRecord()
                    ->lessons()
                    ->pluck('tags')
                    ->flatten()
                    ->unique()
                    ->filter()
                    ->sort()
                    ->mapWithKeys(fn($tag) => [$tag => $tag])
                    ->toArray();
            })
            ->query(function ($query, array $data) {
                if (filled($data['values'])) {
                    return $query->where(function ($query) use ($data) {
                        foreach ($data['values'] as $tag) {
                            $query->orWhereJsonContains('tags', $tag);
                        }
                    });
                }
                return $query;
            })
        ];
    }

    protected function getForm()
    {
        return [
            Hidden::make('school_class_id')->default($this->getOwnerRecord()->id),

            Hidden::make('status')
            ->default(function ($livewire) {
                if (!empty($livewire->mountedActions)) {
                    $firstAction = $livewire->mountedActions[0];
                    if (isset($firstAction['arguments']['column'])) {
                        $column = $firstAction['arguments']['column'];
                        return LessonStatus::tryFrom($column)?->value ?? LessonStatus::TOPICS->value;
                    }
                }
                return LessonStatus::TOPICS->value;
            }),

            Grid::make(2)
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->nullable()
                            ->rows(3)
                            ->columnSpanFull(),

                        Field::tags('tags'),

                        Field::date('completion_date'),

                        Repeater::make('checklist')
                            ->table([
                                TableColumn::make('Item'),
                                TableColumn::make('Done')->width(1),
                            ])
                            ->schema([
                                TextInput::make('item')->placeholder('Enter checklist item'),

                                Toggle::make('done')
                                    ->default(false)
                            ])
                            ->compact()
                            ->minItems(0)
                            ->defaultItems(0),
                    ])
                    ->columnSpan(1),

                Section::make()
                    ->schema([
                        // TODO:: attach multiple files
                    ])
                    ->columnSpan(1),
            ]),
        ];
    }
}
