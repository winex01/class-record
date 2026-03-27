<?php

namespace App\Filament\Columns;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;

class DateTimeColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->toggleable(isToggledHiddenByDefault: false)
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->wrap()
            ->dateTime('m/d/Y h:i A')
            ->tooltip(function ($record, $column) {
                $driver = $record->getConnection()->getDriverName();
                $name = $column->getName();
                $value = $record->{$name};

                if ($driver === 'sqlite') {
                    return 'Search format: ' . Carbon::parse($value)->format('m/d/Y');
                }

                return null;
            })
            ->searchable(query: function ($query, string $search, $column) {
                $name = $column->getName();
                $driver = $query->getConnection()->getDriverName();

                return $query->where(function ($query) use ($name, $search, $driver) {
                    if ($driver === 'sqlite') {
                        $query->whereRaw("strftime('%m/%d/%Y', {$name}) LIKE ?", ["%{$search}%"]);
                    } else {
                        $query->whereRaw("DATE_FORMAT({$name}, '%m/%d/%Y %h:%i %p') LIKE ?", ["%{$search}%"]);
                    }
                });
            });
    }
}
