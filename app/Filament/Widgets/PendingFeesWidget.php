<?php

namespace App\Filament\Widgets;

use Filament\Tables\Table;
use App\Models\FeeCollection;
use Illuminate\Support\Carbon;
use App\Filament\Columns\TextColumn;
use App\Enums\CompletedPendingStatus;
use App\Filament\Columns\AmountColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\Layout\Split;

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
            ->whereNotNull('date')
            ->where(function ($query) {
                $query->Where('date', '<', now()->today());
            })->count();

        return $count > 0 ? (string) $count : null;
    }

    // TODO:: events CRUD actions, refresh table widget // dont remove this this is my remidner for later tasks

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FeeCollection::query()
                    ->whereNotNull('date')
                    ->where('school_class_id', $this->ownerRecord->id)
                    ->withPaymentStatus(false)
                    ->orderBy('date', 'asc')
            )
            ->heading(false)
            ->searchable(false)
            ->columnManager(false)
            ->emptyStateHeading(false)
            ->emptyStateDescription(false)
            ->defaultPaginationPageOption(5)
            ->columns([
                Split::make([
                    TextColumn::make('name')
                        ->sortable(false)
                        ->grow(true)
                        ->color(fn ($record) =>
                            Carbon::parse($record->date)->startOfDay()->lt(now()->startOfDay())
                                ? 'danger'
                                : null
                        )
                        ->description(function ($record) {
                            $days = (int) now()->startOfDay()->diffInDays(
                                Carbon::parse($record->date)->startOfDay(),
                                false
                            );

                            if ($days === 0) return '🔥 Due Today!';
                            if ($days === 1) return '⚠️ Due Tomorrow!';
                            if ($days > 1) return 'in ' . $days . ' days';

                            return trans_choice(
                                '🚨 :count day overdue|🚨 :count days overdue',
                                abs($days),
                                ['count' => abs($days)]
                            );
                        }),

                    AmountColumn::make('amount')
                        ->sortable(false)
                        ->color(fn ($record) => $record->is_voluntary ? 'gray' : 'info')
                        ->prefix(fn ($record) => $record->is_voluntary ? false : '₱')
                        ->state(fn ($record) => $record->is_voluntary ? 'Voluntary' : $record->amount)
                        ->description(fn ($record) => $record->is_voluntary ? 'Contribution' : 'Amount'),

                    AmountColumn::make('total')
                        ->sortable(false)
                        ->color('primary')
                        ->state(fn ($record) => $record->students()->sum('amount'))
                        ->description('Total Collected'),

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
