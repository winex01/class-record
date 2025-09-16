<?php

namespace App\Observers;

use App\Models\User;
use Database\Seeders\TenantSeeder;

class UserObserver
{
    protected $tenantSeederService;

    public function __construct(TenantSeeder $tenantSeederService)
    {
        $this->tenantSeederService = $tenantSeederService;
    }

    public function created(User $user)
    {
        $this->tenantSeederService->seedTenantData($user);
    }
}
