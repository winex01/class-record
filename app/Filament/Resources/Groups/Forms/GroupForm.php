<?php

namespace App\Filament\Resources\Groups\Forms;

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
