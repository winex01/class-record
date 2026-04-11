<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Widgets\PendingFeesWidget;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassActions;
use App\Filament\Resources\SchoolClasses\Schemas\SchoolClassFeeCollectionForm;
use App\Filament\Resources\SchoolClasses\Tables\SchoolClassFeeCollectionTable;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassFeeCollectionActions;
use App\Filament\Resources\SchoolClasses\Filters\SchoolClassFeeCollectionFilters;

class ManageSchoolClassFeeCollections extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;
    protected static string $relationship = 'feeCollections';

    protected function getHeaderWidgets(): array
    {
        return [
            ...static::myWidgets($this->getOwnerRecord()),

            PendingFeesWidget::make([
                'ownerRecord' => $this->getOwnerRecord(),
            ]),
        ];
    }

    public function getTabs(): array
    {
        return SchoolClassFeeCollectionFilters::getTabs($this->getOwnerRecord());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(SchoolClassFeeCollectionForm::getFields());
    }

    public function getTableQuery(): Builder
    {
        return $this->getOwnerRecord()
            ->feeCollections()
            ->getQuery()
            ->withSum('students as total', 'fee_collection_student.amount')
            ->withExists([
                'students as has_unpaid' => fn($q) => $q
                    ->where(
                        fn($sub) => $sub
                            ->whereNull('fee_collection_student.amount')
                            ->orWhere('fee_collection_student.amount', '<', \DB::raw('fee_collections.amount'))
                    )
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns(SchoolClassFeeCollectionTable::getColumns())
            ->recordActions([
                SchoolClassFeeCollectionActions::takeFeeAction(),
                ViewAction::make()->modalWidth(Width::Large),
                EditAction::make()->modalWidth(Width::Large)
                    ->after(fn($livewire) => $livewire->dispatch('refreshCollapsibleTableWidget')),
                DeleteAction::make()
                    ->after(fn($livewire) => $livewire->dispatch('refreshCollapsibleTableWidget')),
            ])
            ->toolbarActions([
                SchoolClassActions::createWithStudentsAction($this->getOwnerRecord())
                    ->label('New Fee Collection')
                    ->modalWidth(width: Width::Large),
                SchoolClassFeeCollectionActions::overviewAction(),
                DeleteBulkAction::make()
                    ->after(fn($livewire) => $livewire->dispatch('refreshCollapsibleTableWidget')),
            ])
            ->recordAction('takeFeeCollectionRelationManager');
        ;
    }
}
