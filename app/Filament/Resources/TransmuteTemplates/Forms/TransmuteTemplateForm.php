<?php

namespace App\Filament\Resources\TransmuteTemplates\Forms;

use App\Filament\Fields\TextInput;

class TransmuteTemplateForm
{
    public static function schema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                // unique combine tenant/user_id and name column
                ->unique(
                    table: 'transmute_templates',
                    modifyRuleUsing: function ($rule) {
                        return $rule->where('user_id', auth()->id());
                    }
                )
        ];
    }
}
