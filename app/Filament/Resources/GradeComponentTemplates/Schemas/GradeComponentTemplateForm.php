<?php

namespace App\Filament\Resources\GradeComponentTemplates\Schemas;

use App\Filament\Fields\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;

class GradeComponentTemplateForm
{
    public static function getFields(): array
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
                ->schema(static::gradeComponentFields())
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

    public static function gradeComponentFields()
    {
        return [
            Grid::make(3)->schema([
                TextInput::make('name')
                    ->placeholder('Enter component name...')
                    ->helperText('You can type or pick from suggestions.')
                    ->required()
                    ->datalist([
                        'Written Works',
                        'Performance Tasks',
                        'Quarterly Assessment',
                        'Quiz',
                        'Exam',
                        'Oral',
                    ])
                    ->columnSpan(2),

                TextInput::make('weighted_score')
                    ->label('Weighted Score')
                    ->helperText('Value between 1-100')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(100)
                    ->step(0.01)
                    ->suffix('%')
                    ->columnSpan(1),
            ])
        ];
    }
}
