<?php

namespace App\Filament\Resources\TransmuteTemplates\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\TransmuteTemplates\TransmuteTemplateResource;

class ManageTransmuteTemplates extends ManageRecords
{
    protected static string $resource = TransmuteTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::Large)
        ];
    }
}
