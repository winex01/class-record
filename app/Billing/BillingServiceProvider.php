<?php

namespace App\Billing;

use App\Billing\SubscribedMiddleware;
use Filament\Billing\Providers\Contracts\BillingProvider;

class BillingServiceProvider implements BillingProvider
{
    public function getRouteAction(): string
    {
        return BillingPage::class;
    }

    public function getSubscribedMiddleware(): string
    {
        return SubscribedMiddleware::class;
    }
}
