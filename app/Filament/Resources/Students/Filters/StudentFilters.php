<?php

namespace App\Filament\Resources\Students\Filters;

use App\Enums\Gender;
use Filament\Tables\Filters\SelectFilter;

class StudentFilters
{
    public static function gender()
    {
        return
            SelectFilter::make('gender')
            ->options(Gender::class)
            ->native(false)
            ->query(function ($query, array $data) {
                return $query->when($data['value'], function ($q) use ($data) {
                    return $q->where('gender', $data['value']);
                });
            });
    }
}
