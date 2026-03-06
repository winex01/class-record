<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Enums\Gender;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Students\Forms\StudentForm;
use App\Filament\Resources\Students\Columns\StudentColumns;
use App\Filament\Resources\Students\Filters\StudentFilters;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassStudents extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

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
        return $schema
            ->components(StudentForm::schema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...static::getColumns(['photo', 'full_name', 'gender', 'birth_date', 'email'])
            ])
            ->filters([
                StudentFilters::gender(),
            ])
            ->recordActions([
                ViewAction::make()->modalWidth(Width::Large),
                EditAction::make()->modalWidth(Width::Large),
                DetachAction::make()->color('warning'),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('New Student')
                    ->modalWidth(Width::Large),

                static::attachAction(),

                static::detachBulkAction(),
            ]);
    }

    public static function attachAction($ownerRecord = null)
    {
        $attachAction = AttachAction::make()
            ->label('Attach Students')
            ->color('info')
            ->multiple()
            ->preloadRecordSelect()
            ->closeModalByClickingAway(false)
            ->recordSelectSearchColumns([
                'last_name',
                'first_name',
                'middle_name',
                'suffix_name',
            ]);

        if ($ownerRecord) {
            $attachAction->recordSelectOptionsQuery(function ($query) use ($ownerRecord) {
                return $query->whereIn('students.id', SchoolClassResource::getClassStudents($ownerRecord->school_class_id));
            });
        }

        return $attachAction;
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

    public static function getColumns($defaultShownColumns = ['photo', 'full_name', 'gender'])
    {
        $columns = StudentColumns::schema();

        foreach ($columns as $key => $col) {
            if (!in_array($col->getName(), $defaultShownColumns)) {
                $columns[$key] = $col->toggleable(isToggledHiddenByDefault: true);
            }
        }

        return $columns;
    }
}
