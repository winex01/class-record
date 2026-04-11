<?php

namespace App\Filament\Resources\MyFiles;

use App\Models\MyFile;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\MyFiles\Schemas\MyFileForm;
use App\Filament\Resources\MyFiles\Pages\ManageMyFiles;
use App\Filament\Resources\MyFiles\Tables\MyFilesTable;
use App\Filament\Resources\MyFiles\Actions\MyFileActions;

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
            ->components(MyFileForm::getFields());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(MyFilesTable::getColumns())
            ->recordActions([
                MyFileActions::viewAction(),
                EditAction::make()->modalWidth(Width::Medium),
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

    public static function getPages(): array
    {
        return [
            'index' => ManageMyFiles::route('/'),
        ];
    }
}
