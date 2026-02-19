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
                    }),

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
                    }),

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
            ])
            ->recordAction('takeAttendanceRelationManager');
    }

    public static function getTakeAttendanceAction()
    {
        return RelationManagerAction::make('takeAttendanceRelationManager')
                ->label('Take Attendance')
                ->modalHeading(fn ($record) => 'Take Attendance - ' . $record->date->format('M d, Y'))
                ->icon(Icon::students())
                ->color('info')
                ->slideOver()
                ->relationManager(TakeAttendanceRelationManager::make());
    }

    public static function getOverviewAction(): Action
    {
        return Action::make('overview')
            ->label('Overview')
            ->color('info')
            ->modalHeading('Student Attendance Overview')
            ->modalDescription(fn ($livewire) => 'Overview of students across all attendance records for ' . $livewire->getOwnerRecord()->name)
            ->modalContent(function ($livewire) {
                $schoolClassId = $livewire->getOwnerRecord()->id;

                return new HtmlString(
                    Blade::render(
                        <<<'BLADE'
                        <div style="margin-top: -1.5rem;" class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                            <strong>Tip:</strong> Click on the present/absent counts to view the specific dates.
                        </div>
                        <div>
                            @livewire('attendance-overview', ['schoolClassId' => $schoolClassId])
                        </div>
                        BLADE,
                        ['schoolClassId' => $schoolClassId]
                    )
                );
            })
            ->modalWidth(Width::TwoExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelAction(false);;
    }
}
