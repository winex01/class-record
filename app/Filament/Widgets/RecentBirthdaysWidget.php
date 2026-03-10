<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Widgets\CollapsibleTableWidget;

class RecentBirthdaysWidget extends CollapsibleTableWidget
{
    protected static ?string $heading = '🎉 Recent Birthdays';
    protected int | string | array $columnSpan = 'half';
    public ?Model $ownerRecord = null;

    public function table(Table $table): Table
    {
        $columns = UpcomingBirthdaysWidget::columnSchema();
        $columns[0]->getComponents()['full_name']->description(fn ($record) =>
            trans_choice(
                ':count day ago|:count days ago',
                $days = abs((int) now()->diffInDays(Carbon::parse($record->birth_date)->setYear(now()->year))),
                ['count' => $days]
            )
        );

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
            ->columns($columns);
    }
}
