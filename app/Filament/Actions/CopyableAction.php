<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class CopyableAction extends Action
{
    public static function make(?string $name = 'copy'): static
    {
        return parent::make($name)
            ->icon(Heroicon::ClipboardDocumentList)
            ->alpineClickHandler("
                let input = \$event.target.closest('.fi-input-wrp').querySelector('input');
                let temp = document.createElement('textarea');
                temp.value = input.value;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                \$tooltip('Copied!', { timeout: 1500 });
            ");
    }
}
