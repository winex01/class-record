<?php

namespace App\Filament\Resources\MyFiles\Pages;

use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\MyFiles\MyFileResource;

class ManageMyFiles extends ManageRecords
{
    protected static string $resource = MyFileResource::class;
}
