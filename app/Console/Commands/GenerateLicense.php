<?php

// TODO: REMOVE THIS BEFORE BUNDLING TO EXE - DEVELOPMENT ONLY

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateLicense extends Command
{
    protected $signature = 'license:generate {app_id} {expires_at}';
    protected $description = 'Generate a signed license file';

    public function handle()
    {
        $data = json_encode([
            'app_id' => $this->argument('app_id'),
            'expires_at' => $this->argument('expires_at'),
        ]);

        $privateKey = file_get_contents(base_path('private.pem'));

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $license = json_encode([
            'app_id' => $this->argument('app_id'),
            'expires_at' => $this->argument('expires_at'),
            'signature' => base64_encode($signature),
        ], JSON_PRETTY_PRINT);

        if (!is_dir(storage_path('licenses'))) {
            mkdir(storage_path('licenses'), 0755, true);
        }

        file_put_contents(storage_path('licenses/license.lic'), $license);

        $this->info('License generated: licenses/license.lic');
    }
}
