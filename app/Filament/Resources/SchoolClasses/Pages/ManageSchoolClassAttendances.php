<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Services\Icon;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\RelationManagers\Attendances\TakeAttendanceRelationManager;

class ManageSchoolClassAttendances extends ManageRelatedRecords
{
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
            ->columns([
                Column::text('date'),

                Column::text('absent')
                    ->searchable(false)
                    ->badge()
                    ->color('danger')
                    ->state(fn ($record) => $record->students()->wherePivot('present', false)->count()),

                Column::text('present')
                    ->searchable(false)
                    ->badge()
                    ->color('success')
                    ->state(fn ($record) => $record->students()->wherePivot('present', true)->count()),

            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('date_from')
                            ->native(false)
                            ->label('Date From'),

                        DatePicker::make('date_to')
                            ->native(false)
                            ->label('Date To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn ($q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['date_to'], fn ($q, $date) => $q->whereDate('date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        if (($data['date_from'] ?? null) || ($data['date_to'] ?? null)) {
                            $from = $data['date_from'] ? \Carbon\Carbon::parse($data['date_from'])->format('M j, Y') : 'Start';
                            $to = $data['date_to'] ? \Carbon\Carbon::parse($data['date_to'])->format('M j, Y') : 'End';

                            return ["Date: {$from} - {$to}"];
                        }

                        return [];
                    })
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::Medium)
                    ->after(function ($record, $data, $action) {
                        $record->students()->sync(SchoolClassResource::getClassStudents($this->getOwnerRecord()));
                    })
            ])
            ->recordActions([
                ActionGroup::make([
                    RelationManagerAction::make('takeAttendanceRelationManager')
                        ->label('Take Attendance')
                        ->icon(Icon::students())
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
