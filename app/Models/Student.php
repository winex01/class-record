<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope('ordered', function ($builder) {
            $builder->orderBy('last_name')
                    ->orderBy('first_name')
                    ->orderBy('middle_name');
        });

        static::updating(function ($student) {
            if ($student->isDirty('photo')) {
                Storage::delete($student->getOriginal('photo'));
            }
        });

        static::deleting(function ($student) {
            if ($student->photo) {
                Storage::delete($student->photo);
            }
        });
    }
}
