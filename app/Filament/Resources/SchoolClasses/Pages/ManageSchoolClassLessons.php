<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Models\Lesson;
use Livewire\Attributes\On;
use Filament\Schemas\Schema;
use Relaticle\Flowforge\Board;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Relaticle\Flowforge\Contracts\HasBoard;
use App\Filament\Widgets\LessonRemindersWidget;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use Relaticle\Flowforge\Concerns\InteractsWithBoard;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Forms\SchoolClassLessonForm;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassLessonActions;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassLessonColumns;

class ManageSchoolClassLessons extends ManageRelatedRecords implements HasBoard
{
    use InteractsWithBoard;
    use ManageSchoolClassInitTrait;

    protected string $view = 'flowforge::filament.pages.board-page';
    protected static string $resource = SchoolClassResource::class;
    protected static string $relationship = 'lessons';
    protected static ?string $model = Lesson::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ...static::myWidgets($this->getOwnerRecord()),

            LessonRemindersWidget::make([
                'ownerRecord' => $this->getOwnerRecord(),
            ]),
        ];
    }

    #[On('kanban-card-moved')]
    public function onCardMoved(): void
    {
        $this->dispatch('refreshCollapsibleTableWidget');
    }

    public function board(Board $board): Board
    {
        return $board
            ->query($this->getOwnerRecord()->lessons()->getQuery())
            ->recordTitleAttribute('title')
            ->columnIdentifier('status')
            ->positionIdentifier('position')
            ->columns(SchoolClassLessonColumns::boardSchema())
            ->cardSchema(fn(Schema $schema) => $schema->components(SchoolClassLessonColumns::cardSchema()))
            ->searchable(['title', 'description', 'tags_search', 'completion_date']) // tags_search = virtual col
            ->columnActions([
                // NOTE:: We do it this way bec. when use ->visible it only disabled it perhaps its because of the board plugin flowforge that i use.
                ...($this->getOwnerRecord()->active ? [
                    CreateAction::make()
                        ->schema(SchoolClassLessonForm::schema($this->getOwnerRecord()))
                        ->hiddenLabel()->iconButton()
                        ->icon('heroicon-o-plus')
                        ->model(static::$model)
                        ->modalWidth(Width::TwoExtraLarge)
                        ->after(function ($livewire) {
                            $livewire->form->saveRelationships();
                            $livewire->dispatch('refreshCollapsibleTableWidget');
                        })
                ] : [])
            ])
            ->cardActions([
                SchoolClassLessonActions::downloadFilesAction(),
                ViewAction::make()
                    ->modalWidth(Width::TwoExtraLarge)
                    ->schema(SchoolClassLessonForm::schema($this->getOwnerRecord())),
                EditAction::make()
                    ->modalWidth(Width::TwoExtraLarge)
                    ->schema(SchoolClassLessonForm::schema($this->getOwnerRecord()))
                    ->after(function ($livewire) {
                        $livewire->form->saveRelationships();
                        $livewire->dispatch('refreshCollapsibleTableWidget');
                    }),
                DeleteAction::make()
                    ->after(function ($livewire) {
                        $livewire->dispatch('refreshCollapsibleTableWidget');
                    }),
            ])
            ->cardAction('view');
    }

    protected function getHeaderActions(): array
    {
        return [
            SchoolClassLessonActions::allAttachedFilesAction($this->getOwnerRecord())
        ];
    }
}
