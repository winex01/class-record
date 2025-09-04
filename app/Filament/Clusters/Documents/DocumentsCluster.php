<?php

namespace App\Filament\Clusters\Documents;

use UnitEnum;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class DocumentsCluster extends Cluster
{
    protected static string | UnitEnum | null $navigationGroup = \App\Enums\NavigationGroup::Group1;
    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static ?int $navigationSort = 200;

    public static function getNavigationIcon(): string | BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return \App\Services\Icon::documents();
    }
}
