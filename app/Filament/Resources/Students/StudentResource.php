<?php

namespace App\Filament\Resources\Students;

use BackedEnum;
use App\Enums\Gender;
use App\Models\Student;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Students\Pages\ManageStudents;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $recordTitleAttribute = 'last_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Student Information')
                ->description("Fill out the student's full details, including contact information, to create their profile.")
                ->aside()
                ->schema([
                    FileUpload::make('photo')
                        ->directory('student-photos')
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

                ])->columnSpanFull(),
            ]);
    }

    // TODO:: add tab for Male/Female
    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                Column::image('photo'),

                Column::text('full_name')
                    ->tooltip(fn ($record) => $record->complete_name)
                    ->sortable(query: function ($query, $direction) {
                        $query->orderBy('last_name', $direction)
                            ->orderBy('first_name', $direction)
                            ->orderBy('middle_name', $direction)
                            ->orderBy('suffix_name', $direction);
                    })
                    ->searchable(['last_name', 'first_name', 'middle_name', 'suffix_name']),

                Column::enum('gender', Gender::class),
                Column::text('birth_date'),
                Column::text('email'),
                Column::text('contact_number')->label('Contact'),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->options(Gender::class)
                    ->query(function ($query, array $data) {
                        return $query->when($data['value'], function ($q) use ($data) {
                            return $q->where('gender', $data['value']);
                        });
                    })
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStudents::route('/'),
        ];
    }
}
