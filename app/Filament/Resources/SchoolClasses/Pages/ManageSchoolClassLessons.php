<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\Column;
use Filament\Tables\Columns\TextColumn;
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

    public function board(Board $board): Board
    {
        return $board
            ->query($this->getOwnerRecord()->lessons()->getQuery())
            ->recordTitleAttribute('title')
            ->columnIdentifier('status')
            ->positionIdentifier('position')
            ->columns([
                Column::make('todo')->label('To Do')->color('gray'),
                Column::make('in_progress')->label('In Progress')->color('blue'),
                Column::make('completed')->label('Completed')->color('green'),
            ])
            ->filters([
                // NOTE:: this board is just a hacky way solution i did because there is no ManageRelatedRecords example
                // available in the plugin https://relaticle.github.io/flowforge/, i notice as long as i have
                // at least 1 filter which is not hidden the ->searchable(['title']) below will work no need to
                // manually add the logic in ->query() using $this->tableSearch.
                SelectFilter::make('status')
                ->options([
                    'todo' => 'To Do',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                ])
                ->label('Filter by Status'),
            ])
            ->searchable(['title']);
    }
}
