<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\RelationManagers\TakeAttendanceRelationManager;

class ManageSchoolClassAttendances extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'attendances';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Field::date('date')
                    ->required()
                    ->date()
                    ->default(now())
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->defaultSort('date', 'desc')
            ->columns([
                Column::date('date'),

                Column::text('present')
                    ->searchable(false)
                    ->badge()
                    ->color('info')
                    ->state(fn ($record) => $record->students()->wherePivot('present', true)->count()),

                Column::text('absent')
                    ->searchable(false)
                    ->badge()
                    ->color('danger')
                    ->state(fn ($record) => $record->students()->wherePivot('present', false)->count()),

            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        Flatpickr::make('date_range')
                            ->showMonths(2)
                            ->rangePicker()
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['date_range'])) {
                            $dates = explode(' to ', $data['date_range']);
                            $dateFrom = $dates[0] ?? null;
                            $dateTo = $dates[1] ?? null;

                            return $query
                                ->when($dateFrom, fn ($q, $date) => $q->whereDate('date', '>=', $date))
                                ->when($dateTo, fn ($q, $date) => $q->whereDate('date', '<=', $date));
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        if (!empty($data['date_range'])) {
                            $dates = explode(' to ', $data['date_range']);
                            $from = isset($dates[0]) ? \Carbon\Carbon::parse($dates[0])->format('M j, Y') : 'Start';
                            $to = isset($dates[1]) ? \Carbon\Carbon::parse($dates[1])->format('M j, Y') : 'End';

                            return ["Date: {$from} to {$to}"];
                        }

                        return [];
                    })
            ])
            ->headerActions([
                SchoolClassResource::createAction($this->getOwnerRecord())
                    ->modalWidth(Width::Medium),

                static::getOverviewAction(),
            ])
            ->recordActions([
                ActionGroup::make([
                    static::getTakeAttendanceAction(),
                    EditAction::make()->modalWidth(Width::Medium),
                    DeleteAction::make(),
                ])->grouped()
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordAction('takeAttendanceRelationManager');
    }

    public static function getTakeAttendanceAction()
    {
        return RelationManagerAction::make('takeAttendanceRelationManager')
                ->label('Take Attendance')
                ->modalHeading(fn ($record) => 'Take Attendance - ' . $record->date->format('M d, Y'))
                ->icon(\App\Services\Icon::students())
                ->color('info')
                ->slideOver()
                ->relationManager(TakeAttendanceRelationManager::make());
    }

    public static function calculateStudentsAttendanceData($attendances): array
    {
        $studentsData = [];

        foreach ($attendances as $attendance) {
            foreach ($attendance->students as $student) {
                if (!isset($studentsData[$student->id])) {
                    $studentsData[$student->id] = [
                        'id' => $student->id,
                        'name' => $student->full_name,
                        'present_count' => 0,
                        'absent_count' => 0,
                    ];
                }

                // Count based on the 'present' boolean pivot column
                if ($student->pivot->present) {
                    $studentsData[$student->id]['present_count']++;
                } else {
                    $studentsData[$student->id]['absent_count']++;
                }
            }
        }

        return array_values($studentsData);
    }

    public static function getOverviewAction(): Action
    {
        return Action::make('overview')
            ->label('Overview')
            ->color('info')
            ->modalHeading('Student Attendance Overview')
            ->modalDescription(fn ($livewire) => 'Overview of students across all attendance records for ' . $livewire->getOwnerRecord()->name)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalContent(function ($livewire) {
                $attendances = $livewire->getOwnerRecord()->attendances()->with('students')->get();
                $studentsData = static::calculateStudentsAttendanceData($attendances);

                // Filter for perfect attendance (zero absents)
                $perfectAttendanceData = array_filter($studentsData, function($student) {
                    return $student['absent_count'] === 0;
                });
                $perfectAttendanceData = array_values($perfectAttendanceData);

                $schoolClassId = $livewire->getOwnerRecord()->id;
                $activeTab = 'all';

                return view('filament.components.attendance-overview',
                    compact(
                        'studentsData',
                        'perfectAttendanceData',
                        'schoolClassId',
                        'activeTab',
                    )
                );
            });
    }
}
