<?php

namespace App\Filament\Resources\SchoolClasses\Colulmns;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TextColumn;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Builder;

class SchoolClassAttendanceColumns
{
    public static function schema()
    {
        return [
            DateColumn::make('date'),

            TextColumn::make('present')
                ->searchable(false)
                ->color('success')
                ->alignCenter()
                ->underline()
                ->state(fn ($record) => $record->students()->wherePivot('present', true)->count())
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query
                        ->withCount([
                            'students as present_count' => function ($query) {
                                $query->where('attendance_student.present', true);
                            }
                        ])
                        ->orderBy('present_count', $direction);
                })
                ->action(
                Action::make('presentStudents')
                        ->modalWidth(Width::Large)
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->modalHeading(fn ($record): string => "Present Students on " . $record->date_formatted)
                        ->modalContent(fn ($record) => new HtmlString(
                            Blade::render(
                                "@livewire('student-attendance-present-absent', ['attendance' => \$attendance, 'isPresent' => true])",
                                ['attendance' => $record]
                            )
                        ))
                ),

            TextColumn::make('absent')
                ->searchable(false)
                ->color('danger')
                ->alignCenter()
                ->underline()
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:underline hover:text-primary-600',
                ])
                ->state(fn ($record) => $record->students()->wherePivot('present', false)->count())
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query
                        ->withCount([
                            'students as absent_count' => function ($query) {
                                $query->where('attendance_student.present', false);
                            }
                        ])
                        ->orderBy('absent_count', $direction);
                })
                ->action(
                Action::make('absentStudents')
                        ->modalWidth(Width::Large)
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->modalHeading(fn ($record) => "Absent Students on " . $record->date_formatted)
                        ->modalContent(fn ($record) => new HtmlString(
                            Blade::render(
                                "@livewire('student-attendance-present-absent', ['attendance' => \$attendance, 'isPresent' => false])",
                                ['attendance' => $record]
                            )
                        ))
                ),
        ];
    }
}
