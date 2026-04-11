<?php

namespace App\Filament\Resources\Students;

use App\Models\Student;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\Students\Schemas\StudentForm;
use App\Filament\Resources\Students\Pages\ManageStudents;
use App\Filament\Resources\Students\Tables\StudentsTable;
use App\Filament\Resources\Students\Filters\StudentFilters;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getNavigationIcon(): string | \BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedUser;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['last_name', 'first_name', 'middle_name', 'suffix_name'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(StudentForm::getFields());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns(StudentsTable::getColumns())
            ->filters([StudentFilters::gender()])
            ->recordActions([
                ViewAction::make()->modalWidth(Width::Large),
                EditAction::make()->modalWidth(Width::Large),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('New Student')
                    ->modalWidth(Width::Large),

                DeleteBulkAction::make(),
            ])
            ->recordAction('edit');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStudents::route('/'),
        ];
    }

    public static function defaultNameSort($direction = 'asc')
    {
        return function ($query) use ($direction) {
            $query->orderBy('last_name', $direction)
                ->orderBy('first_name', $direction)
                ->orderBy('middle_name', $direction)
                ->orderBy('suffix_name', $direction);
        };
    }
}
