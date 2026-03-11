<?php

namespace App\Filament\Widgets;

use Filament\Tables\Table;
use App\Models\FeeCollection;
use Illuminate\Support\Carbon;
use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TextColumn;
use App\Enums\CompletedPendingStatus;
use App\Filament\Columns\AmountColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\Layout\Split;
use App\Filament\Columns\BooleanIconColumn;

class PendingFeesWidget extends CollapsibleTableWidget
{
    protected static ?string $heading = '💰 Pending Fees';
    protected int | string | array $columnSpan = 'full';

    public ?Model $ownerRecord = null;

    public function getCollapsibleBadgeColor(): string
    {
        return 'danger';
    }

    public function getCollapsibleBadge(): ?string
    {
        $count = FeeCollection::query()
            ->where('school_class_id', $this->ownerRecord->id)
            ->withPaymentStatus(false)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FeeCollection::query()
                    ->where('school_class_id', $this->ownerRecord->id)
                    ->withPaymentStatus(false)
            )
            ->heading(false)
            ->searchable(false)
            ->columnManager(false)
            ->emptyStateHeading(false)
            ->emptyStateDescription(false)
            ->defaultPaginationPageOption(5)
            ->columns([
                Split::make([
                    TextColumn::make('name')->sortable(false),

                    AmountColumn::make('amount')
                        ->sortable(false)
                        ->color('info')
                        ->placeholder('—')
                        ->getStateUsing(fn ($record) => $record->amount > 0 ? $record->amount : null),

                    AmountColumn::make('total')
                        ->sortable(false)
                        ->color('primary')
                        ->state(fn ($record) => $record->students()->sum('amount'))
                        ->tooltip('Total Collected'),

                    TextColumn::make('status')
                        ->sortable(false)
                        ->state(function ($record) {
                            return $record->is_completed ? CompletedPendingStatus::COMPLETED->getLabel() : CompletedPendingStatus::PENDING->getLabel();
                        })
                        ->badge()
                        ->color('danger')
                        ->description(fn ($record) => Carbon::parse($record->date)->format('M d, Y')),
                ])
            ]);
    }
}
