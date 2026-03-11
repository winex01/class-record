<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Assessment;
use Filament\Tables\Table;
use App\Filament\Columns\TextColumn;
use App\Enums\CompletedPendingStatus;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\Layout\Split;

class UpcomingAssessmentsWidget extends CollapsibleTableWidget
{
    protected static ?string $heading = '📝 Upcoming Assessments';
    protected int | string | array $columnSpan = 'full';

    public ?Model $ownerRecord = null;

    public function getCollapsibleBadge(): int|string|null
    {
        return Assessment::query()
            ->where('school_class_id', $this->ownerRecord->id)
            ->whereNotNull('date')
            ->withScoreStatus(false)
            ->where(function ($query) {
                $query->Where('date', '<', now()->today());
            })->count();
    }

    public function getCollapsibleBadgeColor(): string
    {
        return 'danger';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Assessment::query()
                ->where('school_class_id', $this->ownerRecord->id)
                ->whereNotNull('date')
                ->withScoreStatus(false)
                ->where(function ($query) {
                    $query->where('date', '<=', now()->addDays(7))
                        ->orWhere('date', '<', now()->today());
                })
                ->orderByRaw("CASE WHEN date < NOW() THEN 0 ELSE 1 END ASC")
                ->orderBy('date', 'ASC')
            )
            ->heading(false)
            ->searchable(false)
            ->columnManager(false)
            ->emptyStateHeading(false)
            ->emptyStateDescription(false)
            ->defaultPaginationPageOption(5)
            ->extraAttributes(['class' => '!border-none !shadow-none !ring-0'])
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

                            if ($days === 0) return '🔥Due Today!';
                            if ($days === 1) return '⚠️ Due Tomorrow!';
                            if ($days > 1) return 'in ' . $days . ' days';

                            return trans_choice(
                                '🚨 :count day overdue|🚨 :count days overdue',
                                abs($days),
                                ['count' => abs($days)]
                            );
                        }),


                    TextColumn::make('assessmentType.name')
                        ->color('primary')
                        ->description(fn ($record) => 'Max Score: '. $record->max_score),

                    TextColumn::make('status')
                        ->state(function ($record) {
                            $state = $record->students()
                                ->whereNull('score')
                                ->exists();

                            return $state ? CompletedPendingStatus::PENDING->getLabel() : CompletedPendingStatus::COMPLETED->getLabel();
                        })
                        ->badge()
                        ->color('danger')
                        ->description(fn ($record) => Carbon::parse($record->date)->format('M d, Y')),


                    TextColumn::make('myFile.path')
                        ->sortable(false)
                        ->html()
                        ->state(fn ($record) => $record->myFile
                            ? collect($record->myFile->path)
                                ->map(fn ($path, $index) =>
                                    '<a href="' .
                                        route('filament.app.myfile.download', ['myFileId' => $record->myFile->id, 'index' => $index]) .
                                    '" class="text-info-500 hover:text-info-600 hover:underline inline" target="_blank">' .
                                    basename($path) . '</a>'
                                )
                                ->join('<span class="mx-1">, </span>')
                            : null
                        )
                        ->description(fn ($record) => $record->myFile->name),



                ]),
            ]);
    }
}
