<?php

namespace App\Filament\Resources\SchoolClasses\RelationManagers;

use App\Services\Column;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;
use App\Enums\FeeCollectionStatus;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class TakeFeeCollectionRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make()
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->count()
                ),
        ];

        if ((float) $this->getOwnerRecord()->amount === 0.0) { // open contribution
            $tabs[FeeCollectionStatus::PAID->getLabel()] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('amount', '>', 0))
                ->badgeColor(FeeCollectionStatus::PAID->getColor())
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->where('amount', '>', 0)->count()
                );

            $tabs[FeeCollectionStatus::UNPAID->getLabel()] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function ($query) {
                    $query->where('amount', 0)->orWhereNull('amount');
                }))
                ->badgeColor(FeeCollectionStatus::UNPAID->getColor())
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->where(function ($query) {
                        $query->where('amount', 0)->orWhereNull('amount');
                    })->count()
                );
        } else {
            foreach (FeeCollectionStatus::cases() as $tab) {
                $tabs[$tab->getLabel()] = Tab::make()
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $tab->value))
                    ->badgeColor($tab->getColor())
                    ->badge(fn () =>
                        $this->getOwnerRecord()->{static::$relationship}()->where('status', $tab->value)->count()
                    );
            }
        }

        return $tabs;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),

                Column::textInput('amount')
                    ->placeholder(
                        $this->getOwnerRecord()->amount == 0
                            ? '₱'
                            : 'Fee ₱' . ($this->getOwnerRecord()->amount ?? 0)
                    )
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
                    ->visible($this->getOwnerRecord()->amount > 0 ? true : false)
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
                // Mark as Paid Bulk Action
                BulkAction::make('markPaid')
                    ->label('Mark as Paid')
                    ->icon(FeeCollectionStatus::PAID->getIcon())
                    ->color(FeeCollectionStatus::PAID->getColor())
                    ->requiresConfirmation()
                    ->action(function ($records, $livewire) {
                        foreach ($records as $record) {
                            // Check pivot amount first
                            $currentAmount = $record->pivot?->amount;

                            // Only update if amount is empty or zero
                            if (empty($currentAmount) || $currentAmount == 0) {
                                $record->feeCollections()
                                    ->updateExistingPivot(
                                        $livewire->getOwnerRecord()->getKey(),
                                        [
                                            'amount' => $livewire->getOwnerRecord()->amount,
                                            'status' => FeeCollectionStatus::PAID->value,
                                        ]
                                    );
                            }
                        }
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Marked as paid')
                    ->visible($this->getOwnerRecord()->amount > 0 ? true : false),

                // Mark as Unpaid Bulk Action
                BulkAction::make('markUnpaid')
                    ->label('Mark as Unpaid')
                    ->icon(FeeCollectionStatus::UNPAID->getIcon())
                    ->color(FeeCollectionStatus::UNPAID->getColor())
                    ->requiresConfirmation()
                    ->action(function ($records, $livewire) {
                        foreach ($records as $record) {
                            $record->feeCollections()
                                ->updateExistingPivot(
                                    $livewire->getOwnerRecord()->getKey(),
                                    [
                                        'amount' => null,
                                        'status' => FeeCollectionStatus::UNPAID->value,
                                    ]
                                );
                        }
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Marked as unpaid')
                    ->visible($this->getOwnerRecord()->amount > 0 ? true : false),


                ManageSchoolClassStudents::detachBulkAction(),
            ]);
    }
}
