<?php

namespace App\Filament\Resources\StudentClasses;

use BackedEnum;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use App\Models\StudentClass;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TagsColumn;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use App\Filament\Resources\StudentClasses\Pages\ManageStudentClasses;

class StudentClassResource extends Resource
{
    protected static ?string $model = StudentClass::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Class';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('Enter the basic information about this class or subject.')
                    ->aside()
                    ->schema([
                        TextInput::make('name')
                            ->label('Class / Subject Name')
                            ->placeholder('e.g. Math 101 or ENG-201')
                            ->required()
                            ->maxLength(255),

                        Grid::make(2)
                            ->schema([
                                Field::date('date_start')
                                    ->label('Start Date')
                                    ->placeholder('e.g. ' . Carbon::now()->format('M j, Y')), // e.g. Aug 28, 2025

                                Field::date('date_end')
                                    ->label('End Date')
                                    ->placeholder('e.g. ' . Carbon::now()->addMonths(6)->format('M j, Y')), // e.g. Nov 28, 2025
                            ]),

                        TagsInput::make('tags')
                            ->label('Tags')
                            ->hint('Use Tab key or Enter key to add multiple tags')
                            ->placeholder('e.g. 1st Year, Section A, Evening Class')
                            ->separator(',')
                            ->splitKeys(['Tab']),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Brief details about this class or subject... (optional)')
                            ->rows(5),

                        Toggle::make('active')
                            ->label('Active / Archived')
                            ->helperText('Active = editable, Archived = read-only')
                            ->offColor('danger')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-lock-closed')
                            ->default(true),

                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Column::text('name'),
                Column::text('date_start'),
                Column::text('date_end'),
                TagsColumn::make('tags')->separator(',')->badge()->searchable(),
                Column::text('description')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => ManageStudentClasses::route('/'),
        ];
    }
}
