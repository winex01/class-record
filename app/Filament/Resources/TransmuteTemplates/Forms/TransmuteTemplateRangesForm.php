<?php

namespace App\Filament\Resources\TransmuteTemplates\Forms;

use Filament\Schemas\Components\Grid;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassGrades;

class TransmuteTemplateRangesForm
{
    public static function schema()
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
                                $rule->where('transmute_template_id', $this->getOwnerRecord()->getKey())
                        ),
                        'initial_max' => $field->unique(
                            table: 'transmute_template_ranges',
                            column: 'initial_max',
                            modifyRuleUsing: fn ($rule) =>
                                $rule->where('transmute_template_id', $this->getOwnerRecord()->getKey())
                        ),
                        'transmuted_grade' => $field->unique(
                            table: 'transmute_template_ranges',
                            column: 'transmuted_grade',
                            modifyRuleUsing: fn ($rule) =>
                                $rule->where('transmute_template_id', $this->getOwnerRecord()->getKey())
                        ),
                        default => $field,
                    },
                    ManageSchoolClassGrades::rangesField()
                ),
            ])
        ];
    }
}
