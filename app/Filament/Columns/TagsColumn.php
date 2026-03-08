<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TagsColumn as BaseTagsColumn;

class TagsColumn extends BaseTagsColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->toggleable(isToggledHiddenByDefault: false)
            ->wrap()
            ->separator(',')
            ->sortable()
            ->searchable(query: function (Builder $query, string $search, $column): Builder {
                $name = $column->getName();
                return $query->whereRaw('LOWER(' . $name . ') LIKE ?', ['%' . strtolower($search) . '%']);
            });
    }
}
