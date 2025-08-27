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
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\Students\Pages\ManageStudents;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $recordTitleAttribute = 'last_name';

    public static function prefixName()
    {
        $tenantKey = \Illuminate\Support\Str::kebab(static::id());
        $timestamp = now()->format('YmdHis');

        return "{$tenantKey}-{$timestamp}-";
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Student Information')
                ->description("Fill out the student's full details, including contact information, to create their profile.")
                ->aside()
                ->schema([
                    FileUpload::make('photo')
                        ->directory('photos')
                        ->avatar(),

                    TextInput::make('last_name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('first_name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('middle_name')
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

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('last_name')
            ->columns([
                Column::image('photo'),

                TextColumn::make('last_name')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('first_name')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('middle_name')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->sortable(),

                Column::enum('gender', Gender::class),
                Column::text('birth_date'),
                Column::text('email'),
                Column::text('contact_number')->label('Contact'),

            ])
            ->filters([
                //
            ])
            ->recordActions([
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
