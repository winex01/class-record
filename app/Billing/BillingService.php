<?php

namespace App\Billing;

use App\Models\User;

class BillingService
{
    public static function getAppId(User $user): string
    {
        $raw = static::getMachineId() . $user->email;
        $hash = strtoupper(md5($raw));

        return implode('-', str_split($hash, 4));
    }

    public static function getMachineId(): string
    {
        $motherboard = shell_exec('wmic baseboard get serialnumber 2>&1');
        $cpu = shell_exec('wmic cpu get processorid 2>&1');
        $mac = shell_exec('wmic nic where "NetEnabled=true" get MACAddress 2>&1');

        return md5($motherboard . $cpu . $mac);
    }

    public static function verifyLicenseFile(string $path, User $user): array
    {
        if (!file_exists($path)) {
            return ['valid' => false, 'message' => 'License file not found.'];
        }

        $license = json_decode(file_get_contents($path), true);

        if (!$license) {
            return ['valid' => false, 'message' => 'Invalid license file.'];
        }

        // verify signature
        $data = json_encode([
            'app_id' => $license['app_id'],
            'expires_at' => $license['expires_at'],
        ]);

        $publicKey = file_get_contents(storage_path('licenses/public.pem'));
        $verified = openssl_verify($data, base64_decode($license['signature']), $publicKey, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            return ['valid' => false, 'message' => 'License signature is invalid.'];
        }

        // check app_id matches
        if ($license['app_id'] !== static::getAppId($user)) {
            return ['valid' => false, 'message' => 'License does not match this machine.'];
        }

        // check expiry
        if (now()->gt($license['expires_at'])) {
            return ['valid' => false, 'message' => 'License has expired.'];
        }

        return [
            'valid' => true,
            'app_id' => $license['app_id'],
            'signature' => $license['signature'],
            'expires_at' => $license['expires_at'],
            'message' => 'License is valid.',
        ];
    }

    public static function isSubscribed(User $user): bool
    {
        $license = License::where('user_id', $user->id)->first();

        if (!$license) {
            return false;
        }

        $result = static::verifyLicenseFile($license->file_path, $user);

        return $result['valid'];
    }
}
