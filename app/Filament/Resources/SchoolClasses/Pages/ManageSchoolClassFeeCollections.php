<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassActions;
use App\Filament\Resources\SchoolClasses\Forms\SchoolClassFeeCollectionForm;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassFeeCollectionActions;
use App\Filament\Resources\SchoolClasses\Filters\SchoolClassFeeCollectionFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassFeeCollectionColumns;

class ManageSchoolClassFeeCollections extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;
    protected static string $relationship = 'feeCollections';

    public function getTabs(): array
    {
        return SchoolClassFeeCollectionFilters::getTabs($this->getOwnerRecord());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(SchoolClassFeeCollectionForm::schema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns(SchoolClassFeeCollectionColumns::schema())
            ->recordActions([
                SchoolClassFeeCollectionActions::takeFeeAction(),
                ViewAction::make()->modalWidth(Width::Large),
                EditAction::make()->modalWidth(Width::Large),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                SchoolClassActions::createWithStudentsAction($this->getOwnerRecord())
                    ->label('New Fee Collection')
                    ->modalWidth(width: Width::Large),
                SchoolClassFeeCollectionActions::overviewAction(),
                DeleteBulkAction::make(),
            ])
            ->recordAction('takeFeeCollectionRelationManager');;
    }
}
