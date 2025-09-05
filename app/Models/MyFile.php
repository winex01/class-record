<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MyFile extends Model
{
    use BelongsToUser;

    protected $guarded = [];

    protected $casts = [
        'files' => 'array',
        'tags' => 'array',
    ];

    protected static function booted()
    {
        static::updating(function ($myFile) {
            if ($myFile->isDirty('files')) {
                $originalFiles = $myFile->getOriginal('files') ?? [];
                $originalFiles = is_string($originalFiles) ? json_decode($originalFiles, true) : $originalFiles;

                foreach ($originalFiles as $file) {
                    if (! in_array($file, $myFile->files ?? [])) {
                        Storage::delete($file);
                    }
                }
            }
        });

        static::deleting(function ($myFile) {
            foreach ($myFile->files ?? [] as $file) {
                Storage::delete($file);
            }
        });
    }
}
