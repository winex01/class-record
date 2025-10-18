<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
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
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\RelationManagers\TakeAttendanceRelationManager;

class ManageSchoolClassAttendances extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'attendances';

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->count()
                ),
        ];
    }

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
            ])
            ->recordActions([
                ActionGroup::make([
                    RelationManagerAction::make('takeAttendanceRelationManager')
                        ->label('Take Attendance')
                        ->icon(\App\Services\Icon::students())
                        ->color('info')
                        ->slideOver()
                        ->relationManager(TakeAttendanceRelationManager::make()),

                    EditAction::make()->modalWidth(Width::Medium),
                    DeleteAction::make(),
                ])->grouped()
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordAction('takeAttendanceRelationManager');
    }
}
