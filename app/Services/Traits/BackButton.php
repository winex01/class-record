<?php

namespace App\Services\Traits;

trait BackButton
{
    protected function getHeaderActions(): array
    {
        return [
            \App\Services\Action::back(static::$resource)
        ];
    }
}
