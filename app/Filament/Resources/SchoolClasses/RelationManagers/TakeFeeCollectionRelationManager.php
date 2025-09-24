<?php

namespace App\Filament\Resources\SchoolClasses\RelationManagers;

use App\Services\Column;
use Filament\Tables\Table;
use App\Enums\FeeCollectionStatus;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class TakeFeeCollectionRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort('full_name', 'asc')
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),

                Column::textInput('amount')
                    ->placeholder('Fee ₱' . ($this->getOwnerRecord()->amount ?? 0))
                    ->rules(function () {
                        $amount = $this->getOwnerRecord()->amount ?? 0;

                        return $amount > 0
                            ? ['numeric', 'min:0', 'max:' . $amount]
                            : ['numeric', 'min:0'];
                    }),

                Column::select('status')
                    ->options(FeeCollectionStatus::class)
                    ->afterStateUpdated(function ($state, $record) {
                        if ($state === FeeCollectionStatus::PAID->value) {
                            // check pivot amount first
                            $currentAmount = $record->pivot?->amount;

                            if (empty($currentAmount) || $currentAmount == 0) {
                                $record->feeCollections()
                                    ->updateExistingPivot(
                                        $this->getOwnerRecord()->getKey(),
                                        ['amount' => $this->getOwnerRecord()->amount]
                                    );
                            }
                        } elseif ($state === FeeCollectionStatus::UNPAID->value) {
                            $record->feeCollections()
                                ->updateExistingPivot(
                                    $this->getOwnerRecord()->getKey(),
                                    ['amount' => null]
                                );
                        }
                    })


            ])
            ->filters([
                ...StudentResource::getFilters()
            ])
            ->headerActions([
                ManageSchoolClassStudents::attachAction($this->getOwnerRecord()),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                ManageSchoolClassStudents::detachBulkAction(),
            ]);
    }
}
