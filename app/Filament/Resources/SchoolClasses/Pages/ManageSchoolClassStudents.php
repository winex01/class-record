<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Enums\Gender;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassStudents extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'students';

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->count()
                ),

            'Male' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', Gender::MALE->value))
                ->badgeColor(Gender::MALE->getColor())
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->where('gender', Gender::MALE->value)->count()
                ),

            'Female' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', Gender::FEMALE->value))
                ->badgeColor(Gender::FEMALE->getColor())
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->where('gender', Gender::FEMALE->value)->count()
                )
        ];
    }

    public function form(Schema $schema): Schema
    {
        return StudentResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                ...static::getColumns()
            ])
            ->filters([
                ...StudentResource::getFilters()
            ])
            ->headerActions([
                CreateAction::make(),
                static::attachAction(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DetachAction::make()->color('warning'),
                ])->grouped()
            ])
            ->toolbarActions([
                static::detachBulkAction(),
            ]);
    }

    public static function attachAction()
    {
        return AttachAction::make()
            ->label('Attach students')
            ->closeModalByClickingAway(false)
            ->preloadRecordSelect()
            ->multiple()
            ->recordSelectSearchColumns([
                'last_name',
                'first_name',
                'middle_name',
                'suffix_name',
            ]); // TODO:: add where clause to query to only show students on this class
    }

    public static function detachBulkAction()
    {
        return DetachBulkAction::make()
                ->color('warning')
                ->action(function ($records, $livewire) {
                    /** @var \Filament\Resources\Pages\ManageRelatedRecords $livewire */
                    foreach ($records as $record) {
                        $livewire->getRelationship()->detach($record);
                    }
                });
    }

    public static function getColumns()
    {
        $columns = StudentResource::getColumns();

        foreach ($columns as $key => $col) {
            if (!in_array($col->getName(), [
                'photo',
                'full_name',
                'gender',
            ])) {
                $col = $col->toggleable(isToggledHiddenByDefault:true);
                $columns[$key] = $col;
            }
        }

        return $columns;
    }
}
