<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Lesson;
use Filament\Tables\Table;
use App\Enums\LessonStatus;
use App\Filament\Columns\EnumColumn;
use App\Filament\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class LessonRemindersWidget extends CollapsibleTableWidget
{
    protected static ?string $heading = '🔔 Lesson Reminders';

    protected int | string | array $columnSpan = 'full';

    public ?Model $ownerRecord = null;

    public function getCollapsibleBadge(): int|string|null
    {
        $count = Lesson::query()
            ->where('school_class_id', $this->ownerRecord->id)
            ->whereNotNull('completion_date')
            ->where('status', '!=', LessonStatus::DONE->value)
            ->whereDate('completion_date', '<', now()->today())
            ->count();

        return $count > 0 ? $count : null;
    }

    public function getCollapsibleBadgeColor(): string
    {
        return 'danger';
    }

    public function table(Table $table): Table
    {
        $schoolClassId = $this->ownerRecord->id;

        return $table
            ->query(
                Lesson::query()
                ->where('school_class_id', $schoolClassId)
                ->whereNotNull('completion_date')
                ->where('status', '!=', LessonStatus::DONE->value) // ← exclude DONE globally
                ->where(function ($query) {
                    $query->whereBetween('completion_date', [now()->today(), now()->addDays(7)])
                        ->orWhere(function ($q) {
                            $q->where('completion_date', '<', now()->today());
                        });
                })
                ->orderByRaw("CASE WHEN completion_date < NOW() THEN 0 ELSE 1 END ASC")
                ->orderBy('completion_date', 'ASC')
            )
            ->heading(false)
            ->searchable(false)
            ->columnManager(false)
            ->emptyStateHeading(false)
            ->emptyStateDescription(false)
            ->defaultPaginationPageOption(5)
            ->columns([
                Split::make([
                    TextColumn::make('title')
                        ->sortable(false)
                        ->grow(true)
                        ->color(fn ($record) =>
                            Carbon::parse($record->completion_date)->startOfDay()->lt(now()->startOfDay())
                                ? 'danger'
                                : null
                        )
                        ->description(function ($record) {
                            $days = (int) now()->startOfDay()->diffInDays(
                                Carbon::parse($record->completion_date)->startOfDay(),
                                false
                            );

                            if ($days === 0) return '🔥 Due Today!';
                            if ($days === 1) return '⚠️ Due Tomorrow!';
                            if ($days > 1) return 'in ' . $days . ' days';

                            return trans_choice(
                                ':count day overdue|:count days overdue',
                                abs($days),
                                ['count' => abs($days)]
                            );
                        }),

                    TextColumn::make('myFiles.path')
                        ->label('Files')
                        ->sortable(false)
                        ->html()
                        ->getStateUsing(fn ($record) => $record->myFiles
                            ->flatMap(fn ($file) => collect($file->path)
                                ->map(fn ($path, $index) =>
                                    '<a href="' .
                                        route('filament.app.myfile.download', ['myFileId' => $file->id, 'index' => $index]) .
                                    '" class="text-info-500 hover:text-info-600 hover:underline inline" target="_blank">' .
                                    basename($path) . '</a>'
                                )
                            )
                            ->join('<span class="mx-1">, </span>')
                        )
                        ->description(fn($record) => $record->tags),

                    Stack::make([
                        EnumColumn::make('status')
                            ->enum(LessonStatus::class)
                            ->badge()
                            ->sortable(false)
                            ->description(fn ($record) =>
                                $record->completion_date
                                    ? Carbon::parse($record->completion_date)->format('M d, Y')
                                    : null
                            ),

                    ])->grow(false),
                ]),
            ]);
    }
}
