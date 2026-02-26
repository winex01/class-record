<?php

namespace App\Filament\Resources\Students;

use App\Enums\Gender;
use App\Models\Student;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Students\Pages\ManageStudents;

class StudentResource extends Resource
{


    protected static ?string $model = Student::class;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['last_name', 'first_name', 'middle_name', 'suffix_name'];
    }

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return \App\Services\Icon::students();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('photo')
                    ->directory('student-photos')
                    ->maxSize(10000) // 10 MB
                    ->avatar(),

                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('middle_name')
                    ->maxLength(255),

                TextInput::make('suffix_name')
                    ->placeholder('Jr. I, II')
                    ->maxLength(255),

                Field::gender(),

                Field::date('birth_date'),

                TextInput::make('email')
                    ->label('Email address')
                    ->email(),

                Field::phone('contact_number'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns(static::getColumns())
            ->filters(static::getFilters())
            ->recordActions([
                ViewAction::make()->modalWidth(Width::Large),
                EditAction::make()->modalWidth(Width::Large),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordAction('edit');
    }

    public static function getFilters()
    {
        return [
            SelectFilter::make('gender')
                ->options(Gender::class)
                ->native(false)
                ->query(function ($query, array $data) {
                    return $query->when($data['value'], function ($q) use ($data) {
                        return $q->where('gender', $data['value']);
                    });
                })
        ];
    }

    public static function getColumns()
    {
        return [
            Column::image('photo'),

            Column::text('full_name')
                ->tooltip(fn ($record) => $record->complete_name)
                ->sortable(query: function ($query, $direction) {
                    $callback = static::defaultNameSort($direction);
                    $callback($query);
                })
                ->searchable(['last_name', 'first_name', 'middle_name', 'suffix_name']),

            Column::enum('gender', Gender::class),
            Column::date('birth_date'),
            Column::text('email'),
            'contact_number' =>
            Column::text('contact_number')->label('Contact'),
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

    public static function getPages(): array
    {
        return [
            'index' => ManageStudents::route('/'),
        ];
    }

    public static function selectRelationship($studentIds = [])
    {
        return Select::make('student_id')
            ->multiple()
            ->preload()
            ->placeholder('Choose students...')
            ->searchable(['students.first_name', 'students.last_name', 'students.middle_name', 'students.suffix_name'])
            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
            ->relationship(
                name: 'students',
                modifyQueryUsing: fn ($query) => $query->whereIn('students.id', $studentIds)
            )
            ->saveRelationshipsUsing(function ($record, $state) use ($studentIds) {
                $studentIds = ! empty($state) ?
                    $state : $studentIds;

                $record->students()->sync($studentIds);
            });
    }
}
