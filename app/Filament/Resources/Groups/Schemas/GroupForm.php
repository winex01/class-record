<?php

namespace App\Filament\Resources\Groups\Schemas;

use App\Models\Group;

class GroupForm
{
    public static function selectOptions()
    {
        return Group::all()
            ->pluck('name', 'name')
            ->prepend('-', '-')
            ->toArray();
    }
}
