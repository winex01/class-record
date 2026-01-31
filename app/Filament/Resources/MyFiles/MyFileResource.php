<?php

namespace App\Filament\Resources\MyFiles;

use App\Models\MyFile;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\MyFiles\Pages\ManageMyFiles;

class MyFileResource extends Resource
{
    protected static ?string $model = MyFile::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 200;

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return \App\Services\Icon::myFiles();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...static::getForm()
            ]);
    }

    public static function getForm($readonly = false)
    {
        return [
            TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->disabled($readonly),

            Field::tags('tags')
                ->placeholder('e.g. Lesson, Assessment')
                ->disabled($readonly),

            FileUpload::make('path')
                ->label('File')
                ->required()
                ->multiple()
                ->directory('my-files')
                ->downloadable()
                ->openable()
                ->columns(12)
                ->deletable(fn ($operation) => $operation !== 'view')
                ->placeholder(fn ($operation) => $operation === 'view'
                    ? '<strong>Click on the icon to download or view</strong>'
                    : null)
                ->hint('Attach one or more files')
                ->disabled($readonly),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Column::text('name'),
                Column::tags('tags'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View & Download')
                    ->modalWidth(Width::Medium)
                    ->color('info'),

                EditAction::make()
                    ->modalWidth(Width::Medium),

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
            'index' => ManageMyFiles::route('/'),
        ];
    }

    public static function selectMyFileAndCreateOption()
    {
        return Select::make('my_file_id')
                ->relationship('myFile', 'name')
                ->helperText('Optional')
                ->preload()
                ->searchable()
                ->nullable()
                ->createOptionForm(static::getForm(false))
                ->createOptionAction(
                    fn (Action $action) => $action->modalWidth(Width::Medium),
                )
                ->editOptionForm(static::getForm(true))
                ->editOptionAction(function (Action $action) {
                    return $action
                        ->icon('heroicon-o-eye')
                        ->tooltip('View')
                        ->modalHeading('View File Details')
                        ->modalWidth(Width::Medium)
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close');
                });
    }
}
