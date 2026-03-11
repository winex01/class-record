<?php

namespace App\Filament\Widgets;

use App\Enums\Gender;
use App\Models\Student;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use App\Filament\Columns\EnumColumn;
use App\Filament\Columns\TextColumn;
use App\Filament\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\Layout\Split;
use App\Filament\Widgets\CollapsibleTableWidget;

class UpcomingBirthdaysWidget extends CollapsibleTableWidget
{
    protected static ?string $heading = '🎂 Upcoming Birthdays';
    protected int | string | array $columnSpan = 'half';
    public ?Model $ownerRecord = null;

    public function getCollapsibleBadge(): int|string|null
    {
        $count = Student::whereHas('schoolClasses', function ($query) {
                $query->where('school_classes.id', $this->ownerRecord->id);
            })
            ->birthdayToday()
            ->count();

        return $count > 0 ? $count : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::whereHas('schoolClasses', function ($query) {
                    $query->where('school_classes.id', $this->ownerRecord->id);
                })->upcomingBirthdays()
            )
            ->heading(false)
            ->searchable(false)
            ->columnManager(false)
            ->emptyStateHeading(false)
            ->emptyStateDescription(false)
            ->defaultPaginationPageOption(5)
            ->columns(static::columnSchema());
    }

    public static function columnSchema()
    {
        return [
            Split::make([
                ImageColumn::make('photo')
                    ->grow(false)
                    ->imageSize(32),

                'full_name' =>
                TextColumn::make('full_name')
                    ->tooltip(fn ($record) => $record->complete_name)
                    ->searchable(['last_name', 'first_name', 'middle_name', 'suffix_name'])
                    ->sortable(false)
                    ->grow(true)
                    ->color(fn ($record) =>
                        Carbon::parse($record->birth_date)->format('m-d') === now()->format('m-d')
                            ? 'primary'
                            : null
                    )
                    ->description(function ($record) {
                        if (Carbon::parse($record->birth_date)->format('m-d') === now()->format('m-d')) {
                            return '🎂 Today!';
                        }
                        $days = (int) now()->copy()->startOfDay()->diffInDays(
                            Carbon::parse($record->birth_date)->setYear(now()->year)->startOfDay()
                        );
                        return match($days) {
                            1 => '🎉 Tomorrow!',
                            default => 'in ' . $days . ' days'
                        };
                    }),

                    EnumColumn::make('gender')
                        ->enum(Gender::class)
                        ->sortable(false)
                        ->description(fn ($record) => $record->birth_date->format('M d, Y'))
                        ->grow(false),
            ]),
        ];
    }
}
