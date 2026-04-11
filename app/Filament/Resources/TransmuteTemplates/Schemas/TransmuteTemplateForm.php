<?php

namespace App\Filament\Resources\TransmuteTemplates\Schemas;

use App\Filament\Fields\TextInput;

class TransmuteTemplateForm
{
    public static function getFields(): array
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
