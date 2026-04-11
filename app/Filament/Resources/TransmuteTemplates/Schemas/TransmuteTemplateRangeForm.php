<?php

namespace App\Filament\Resources\TransmuteTemplates\Schemas;

use App\Models\TransmuteTemplate;
use App\Filament\Fields\TextInput;
use App\Filament\Fields\NumericInput;
use Filament\Schemas\Components\Grid;

class TransmuteTemplateRangeForm
{
    public static function getFields(TransmuteTemplate $ownerRecord)
    {
        return [
            Grid::make(3)
            ->schema([
                // NOTE: Using modifyRuleUsing() to scope the unique rule to
                // $this->getOwnerRecord()->id so validation only applies per template.
                ...array_map(
                    fn ($field) => match ($field->getName()) {
                        'initial_min' => $field->unique(
                            table: 'transmute_template_ranges',
                            column: 'initial_min',
                            modifyRuleUsing: fn ($rule) =>
                                $rule->where('transmute_template_id', $ownerRecord->getKey())
                        ),
                        'initial_max' => $field->unique(
                            table: 'transmute_template_ranges',
                            column: 'initial_max',
                            modifyRuleUsing: fn ($rule) =>
                                $rule->where('transmute_template_id', $ownerRecord->getKey())
                        ),
                        'transmuted_grade' => $field->unique(
                            table: 'transmute_template_ranges',
                            column: 'transmuted_grade',
                            modifyRuleUsing: fn ($rule) =>
                                $rule->where('transmute_template_id', $ownerRecord->getKey())
                        ),
                        default => $field,
                    },
                    static::getRangeFields()
                ),
            ])
        ];
    }

    public static function getRangeFields()
    {
        return [
            NumericInput::make('initial_min')
                ->required()
                ->minValue(0)
                ->maxValue(100)
                ->placeholder('e.g., 0.00')
                ->live(onBlur: true)
                ->rules([
                    fn ($get) => function (string $attribute, $value, $fail) use ($get) {
                        $maxValue = $get('initial_max');
                        if ($maxValue !== null && $value > $maxValue) {
                            $fail('Minimum must be less than or equal to maximum.');
                        }
                    }
                ])
                ->columnSpan(1),

            NumericInput::make('initial_max')
                ->required()
                ->minValue(0)
                ->maxValue(100)
                ->placeholder('e.g., 99.99')
                ->live(onBlur: true)
                ->rules([
                    fn ($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                        $minValue = $get('initial_min');
                        if ($minValue !== null && $value < $minValue) {
                            $fail('Maximum must be greater than or equal to minimum.');
                        }
                    }
                ])
                ->columnSpan(1),

            TextInput::make('transmuted_grade')
                ->placeholder('e.g., 99, 1.00, A+')
                ->required()
                ->maxLength(10)
                ->columnSpan(1),
        ];
    }
}
