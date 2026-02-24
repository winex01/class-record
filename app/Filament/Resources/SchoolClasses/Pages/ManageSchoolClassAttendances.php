<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Services\Icon;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Blade;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
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
                    ->alignCenter()
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
                            ->modalContent(fn ($record) => new HtmlString(
                                Blade::render(
                                    "@livewire('student-attendance-present-absent', ['attendance' => \$attendance, 'isPresent' => true])",
                                    ['attendance' => $record]
                                )
                            ))
                    ),

                Column::text('absent')
                    ->searchable(false)
                    ->badge()
                    ->color('danger')
                    ->alignCenter()
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
                            ->modalContent(fn ($record) => new HtmlString(
                                Blade::render(
                                    "@livewire('student-attendance-present-absent', ['attendance' => \$attendance, 'isPresent' => false])",
                                    ['attendance' => $record]
                                )
                            ))
                    ),

            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        Field::date('date_from')
                            ->label('From'),
                        Field::date('date_to')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn ($q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['date_to'], fn ($q, $date) => $q->whereDate('date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['date_from'] ?? null) {
                            $indicators[] = 'From: ' . \Carbon\Carbon::parse($data['date_from'])->format('M j, Y');
                        }

                        if ($data['date_to'] ?? null) {
                            $indicators[] = 'To: ' . \Carbon\Carbon::parse($data['date_to'])->format('M j, Y');
                        }

                        return $indicators;
                    })
                    ->columnSpan(2)
            ])
            ->headerActions([
                SchoolClassResource::createAction($this->getOwnerRecord())
                    ->modalWidth(Width::Medium),

                static::getOverviewAction(),
            ])
            ->recordActions([
                static::getTakeAttendanceAction(),
                EditAction::make()->modalWidth(Width::Medium),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getTakeAttendanceAction()
    {
        return RelationManagerAction::make('takeAttendanceRelationManager')
                ->label('Take Attendance')
                ->icon(Icon::students())
                ->color('info')
                ->slideOver()
                ->relationManager(TakeAttendanceRelationManager::make())
                ->modalHeading(fn ($record) => new HtmlString(
                    view('filament.components.attendance-modal-heading', [
                        'record' => $record,
                    ])->render()
                ))
                ;
    }

    public static function getOverviewAction(): Action
    {
        return Action::make('overview')
            ->color('info')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalWidth(Width::TwoExtraLarge)
            ->modalHeading('Student Attendance Overview')
            ->modalDescription(fn ($livewire) => 'Overview of students across all attendance records for ' . $livewire->getOwnerRecord()->name)
            ->modalContent(fn ($livewire) => new HtmlString(
                Blade::render(
                    '<div class="mb-4 text-sm text-gray-600 dark:text-gray-400" style="margin-top:-1.5rem;">
                        <strong>Tip:</strong> Click on the present/absent counts to view the specific dates.
                    </div>
                    @livewire("attendance-overview", ["schoolClassId" => $schoolClassId])',
                    ['schoolClassId' => $livewire->getOwnerRecord()->id]
                )
            ));
    }
}
