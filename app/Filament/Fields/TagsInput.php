<?php

namespace App\Filament\Fields;

use Illuminate\Support\Str;
use Filament\Forms\Components\TagsInput as BaseTagsInput;

class TagsInput extends BaseTagsInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($component): string => Str::headline($component->getName()))
            ->separator(',')
            ->splitKeys(['Tab'])
            ->hint(function ($component) {
                $lowerName = strtolower(str($component->getName())->headline());
                return 'Use Tab key or Enter key to add multiple ' . $lowerName;
            });
    }
}
