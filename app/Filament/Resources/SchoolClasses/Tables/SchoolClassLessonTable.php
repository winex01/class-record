<?php

namespace App\Filament\Resources\SchoolClasses\Tables;

use App\Enums\LessonStatus;
use Relaticle\Flowforge\Column;
use Filament\Support\Enums\TextSize;
use Filament\Infolists\Components\TextEntry;

class SchoolClassLessonTable
{
    public static function cardSchema(): array
    {
        return [
            TextEntry::make('description')
                ->hiddenLabel()
                ->color('gray')
                ->size(TextSize::Small)
                ->lineClamp(3)
                ->html()
                ->hidden(fn($record) => empty($record->description))
                ->extraAttributes(['style' => 'margin-top: -15px;']),

            TextEntry::make('tags')
                ->hiddenLabel()
                ->badge()
                ->separator(',')
                ->color('primary')
                ->size(TextSize::Small)
                ->hidden(fn($record) => empty($record->tags))
                ->extraAttributes(['style' => 'margin-top: -15px;']),

            TextEntry::make('completion_date')
                ->hiddenLabel()
                ->date('M d, Y')
                ->icon('heroicon-o-calendar')
                ->iconColor('primary')
                ->size(TextSize::Small)
                ->hidden(fn($record) => empty($record->completion_date))
                ->extraAttributes(['style' => 'margin-top: -15px;'])
                ->tooltip(
                    fn($record) => $record->completion_date
                    ? 'Search format: ' . $record->completion_date->format('Y-m-d')
                    : null
                )
        ];
    }

    public static function boardSchema()
    {
        return [
            Column::make(LessonStatus::TOPICS->value)
                ->label(LessonStatus::TOPICS->getLabel())
                ->color(LessonStatus::TOPICS->getColor()),
            Column::make(LessonStatus::IN_PROGRESS->value)
                ->label(LessonStatus::IN_PROGRESS->getLabel())
                ->color(LessonStatus::IN_PROGRESS->getColor()),
            Column::make(LessonStatus::DONE->value)
                ->label(LessonStatus::DONE->getLabel())
                ->color(LessonStatus::DONE->getColor()),
            Column::make(LessonStatus::NEED_REVIEW->value)
                ->label(LessonStatus::NEED_REVIEW->getLabel())
                ->color(LessonStatus::NEED_REVIEW->getColor()),
        ];
    }
}
