<?php

namespace App\Billing;

use Filament\Widgets\Widget;
use Filament\Facades\Filament;

class LicenseWidget extends Widget
{
    protected string $view = 'filament.widgets.license-widget';
    protected int|string|array $columnSpan = 'half';

    protected $license;

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }

    public function mount(): void
    {
        $this->license = License::where('user_id', auth()->id())
            ->orderBy('expires_at', 'desc')
            ->first();
    }

    public function getAppId(): string
    {
        return BillingService::getAppId(auth()->user());
    }

    public function getExpiresAt(): string
    {
        if (!$this->license) {
            return 'No active license';
        }

        return $this->license->expires_at->format('M d, Y');
    }

    public function getExpiresAtColor(): string
    {
        if (!$this->license) {
            return 'text-gray-500';
        }

        $days = now()->diffInDays($this->license->expires_at, false);

        return BillingService::trialCssColor($days);
    }
}
