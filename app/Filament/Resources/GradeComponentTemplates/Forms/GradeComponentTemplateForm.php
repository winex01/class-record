<?php

namespace App\Filament\Resources\GradeComponentTemplates\Forms;

use App\Filament\Fields\TextInput;
use Filament\Forms\Components\Repeater;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassGrades;

class GradeComponentTemplateForm
{
    public static function schema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->unique(
                    table: 'grade_component_templates',
                    modifyRuleUsing: function ($rule) {
                        return $rule->where('user_id', auth()->id());
                    }
                ),

            Repeater::make('components')
                ->hiddenLabel()
                ->collapsible()
                ->orderColumn()
                ->minItems(1)
                ->collapsed(fn ($operation) => $operation == 'view' ? true : false)
                ->itemLabel(fn (array $state): ?string =>
                    isset($state['name'], $state['weighted_score'])
                        ? "{$state['name']} ({$state['weighted_score']}%)"
                        : ($state['name'] ?? 'New Component')
                )
                ->schema(ManageSchoolClassGrades::getComponentFields())
                ->rules([
                    fn ($get) => function (string $attribute, $value, $fail) use ($get) {
                        $total = collect($get('components'))->sum('weighted_score');
                        if ($total != 100) {
                            $fail("The total weighted score of all components must equal 100%. Current total: {$total}%");
                        }
                    },
                ])
        ];
    }
}
