<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;

class DateColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->toggleable(isToggledHiddenByDefault: false)
            ->label(fn($column): string => Str::headline($column->getName()))
            ->wrap()
            ->date()
            ->sortable()
            ->searchable(query: function ($query, string $search, $column) {
                $name = $column->getName();
                $driver = $query->getConnection()->getDriverName();

                return $query->where(function ($query) use ($name, $search, $driver) {
                    if ($driver === 'sqlite') {
                        // SQLite: strftime outputs zero-padded day, no abbreviated month
                        $query->whereRaw("strftime('%m/%d/%Y', {$name}) LIKE ?", ["%{$search}%"]);
                    } else {
                        // MySQL
                        $query->whereRaw("DATE_FORMAT({$name}, '%b %d, %Y') LIKE ?", ["%{$search}%"])
                            ->orWhereRaw("DATE_FORMAT({$name}, '%M %d, %Y') LIKE ?", ["%{$search}%"]);
                    }
                });
            })
            ->tooltip(function ($record, $column) {
                $driver = $record->getConnection()->getDriverName();
                $name = $column->getName();
                $value = $record->{$name};

                if ($driver === 'sqlite') {
                    return 'Search format: ' . \Carbon\Carbon::parse($value)->format('m/d/Y');
                }

                return null;
            });
    }
}
