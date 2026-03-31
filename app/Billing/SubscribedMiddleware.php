<?php

namespace App\Billing;

use Closure;
use Illuminate\Http\Request;
use App\Billing\BillingService;
use Symfony\Component\HttpFoundation\Response;

class SubscribedMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!BillingService::isOnTrial(auth()->user()) && !BillingService::isSubscribed(auth()->user())) {
            return redirect()->route('filament.app.tenant.billing');
        }

        return $next($request);
    }
}
