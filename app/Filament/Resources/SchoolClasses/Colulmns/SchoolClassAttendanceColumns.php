<?php

namespace App\Filament\Resources\SchoolClasses\Colulmns;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TextColumn;
use Illuminate\Support\Facades\Blade;

class SchoolClassAttendanceColumns
{
    public static function schema()
    {
        return [
            DateColumn::make('date'),

            TextColumn::make('present_count')
                ->label('Present')
                ->searchable(false)
                ->color('success')
                ->alignCenter()
                ->underline()
                ->sortable()
                ->action(
                    Action::make('presentStudents')
                        ->modalWidth(Width::Large)
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->closeModalByClickingAway()
                        ->closeModalByEscaping()
                        ->modalHeading(fn($record) => "Present Students on " . $record->date_formatted)
                        ->modalContent(fn($record) => new HtmlString(
                            Blade::render(
                                "@livewire('student-attendance-present-absent', ['attendance' => \$attendance, 'isPresent' => true])",
                                ['attendance' => $record]
                            )
                        ))
                ),

            TextColumn::make('absent_count')
                ->label('Absent')
                ->searchable(false)
                ->color('danger')
                ->alignCenter()
                ->underline()
                ->sortable()
                ->action(
                    Action::make('absentStudents')
                        ->modalWidth(Width::Large)
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->closeModalByClickingAway()
                        ->closeModalByEscaping()
                        ->modalHeading(fn($record) => "Absent Students on " . $record->date_formatted)
                        ->modalContent(fn($record) => new HtmlString(
                            Blade::render(
                                "@livewire('student-attendance-present-absent', ['attendance' => \$attendance, 'isPresent' => false])",
                                ['attendance' => $record]
                            )
                        ))
                ),
        ];
    }
}
