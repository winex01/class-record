<?php

namespace App\Billing;

use Filament\Pages\Page;

class BillingPage extends Page
{
    protected string $view = 'filament.resources.pages.billing';
    protected static bool $shouldRegisterNavigation = false;
}
