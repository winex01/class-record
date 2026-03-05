<?php

namespace App\Filament\Resources\MyFiles;

use App\Models\MyFile;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Filament\Fields\Select;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use App\Filament\Fields\TagsInput;
use App\Filament\Fields\TextInput;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\MyFiles\Pages\ManageMyFiles;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MyFileResource extends Resource
{
    protected static ?string $model = MyFile::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 200;

    public static function getNavigationIcon(): string | \BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedClipboardDocument;
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

            TagsInput::make('tags')
                ->placeholder('e.g. Lesson, Assessment')
                ->disabled($readonly),

            FileUpload::make('path')
                ->label('Files')
                ->required()
                ->multiple()
                ->directory(fn () => 'my-files/' . auth()->id())
                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $directory = 'my-files/' . auth()->id();

                    $fileName = "{$originalName}.{$extension}";
                    $counter = 1;

                    while (Storage::disk(config('filament.default_filesystem_disk'))->exists("{$directory}/{$fileName}")) {
                        $fileName = "{$originalName}-{$counter}.{$extension}";
                        $counter++;
                    }

                    return $fileName;
                })
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
                ...static::getColumns(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                static::getViewAction(),

                EditAction::make()
                    ->modalWidth(Width::Medium),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Upload File')
                    ->modalHeading('Upload File')
                    ->modalWidth(Width::Medium),
                DeleteBulkAction::make(),
            ])
            ->recordAction(false);
    }

    public static function getViewAction()
    {
        return ViewAction::make()->modalWidth(Width::Medium);
    }

    public static function getColumns()
    {
        return [
            TextColumn::make('name'),
            TagsColumn::make('tags'),

            TextColumn::make('path')
                ->label('Files')
                ->html()
                ->getStateUsing(fn ($record) => collect($record->path)
                    ->map(fn ($path, $index) =>
                        '<a href="' . route('filament.app.myfile.download', ['myFileId' => $record->id, 'index' => $index]) . '" class="text-info-500 hover:text-info-600 hover:underline inline" target="_blank">' . basename($path) . '</a>'
                    )
                    ->join('<span class="mx-1">, </span>')
                )
        ];
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
