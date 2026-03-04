<?php

namespace App\Filament\Fields;

use Filament\Forms\Components\TagsInput as BaseTagsInput;

class TagsInput extends BaseTagsInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->separator(',')
            ->splitKeys(['Tab'])
            ->hint(function ($component) {
                $lowerName = strtolower($component->getName());
                return 'Use Tab key or Enter key to add multiple ' . $lowerName;
            });
    }
}
