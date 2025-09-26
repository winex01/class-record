<?php

namespace App\Services;

final class SelectOption
{
    public static function yesOrNo()
    {
        return [
            true => 'Yes',
            false => 'No',
        ];
    }
}
