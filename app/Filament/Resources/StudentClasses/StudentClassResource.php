<?php

namespace App\Filament\Resources\StudentClasses;

use BackedEnum;
use Filament\Tables\Table;
use App\Models\StudentClass;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
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
                        // TODO:: placeholder

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Grid::make(2)
                            ->schema([
                                DatePicker::make('date_start')
                                    ->label('Start Date')
                                    ->native(false),

                                DatePicker::make('date_end')
                                    ->label('End Date')
                                    ->native(false),
                            ]),

                        TagsInput::make('tags')
                            ->separator(',')
                            ->splitKeys(['Tab']),

                        Textarea::make('description')
                            ->rows(5),

                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStudentClasses::route('/'),
        ];
    }
}
