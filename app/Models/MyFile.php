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
        'path' => 'array',
        'tags' => 'array',
    ];

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    protected static function booted()
    {
        static::updating(function ($myFile) {
            if ($myFile->isDirty('path')) {
                $originalFiles = $myFile->getOriginal('path') ?? [];
                $originalFiles = is_string($originalFiles) ? json_decode($originalFiles, true) : $originalFiles;

                foreach ($originalFiles as $file) {
                    if (! in_array($file, $myFile->path ?? [])) {
                        Storage::delete($file);
                    }
                }
            }
        });

        static::deleting(function ($myFile) {
            foreach ($myFile->path ?? [] as $file) {
                Storage::delete($file);
            }
        });
    }
}
