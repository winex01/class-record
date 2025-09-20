<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use BelongsToUser;

    protected $guarded = [];

    public function schoolClasses()
    {
        return $this->belongsToMany(SchoolClass::class)->withTimestamps();
    }

    public function attendances()
    {
        return $this->belongsToMany(Attendance::class)
            ->withTimestamps()
            ->withPivot(['present']);
    }

    public function assessments()
    {
        return $this->belongsToMany(Assessment::class)
            ->withTimestamps()
            ->withPivot(['score', 'group']);
    }

    public function feeCollections()
    {
        return $this->belongsToMany(FeeCollection::class)
            ->withTimestamps()
            ->withPivot(['amount', 'status']);
    }

    protected static function booted()
    {
        static::addGlobalScope('ordered', function ($builder) {
            $builder->orderBy('last_name')
                    ->orderBy('first_name')
                    ->orderBy('middle_name')
                    ->orderBy('suffix_name');
        });

        static::updating(function ($student) {
            if ($student->isDirty('photo') && !empty($student->getOriginal('photo'))) {
                Storage::delete($student->getOriginal('photo'));
            }
        });

        static::deleting(function ($student) {
            if (!empty($student->photo)) {
                Storage::delete($student->photo);
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        // Middle initial if middle_name exists
        $middleInitial = $this->middle_name ? strtoupper(substr($this->middle_name, 0, 1)) . '.' : '';

        // Build name parts array
        $nameParts = array_filter([
            $this->last_name,
            "{$this->first_name}" . ($middleInitial ? " {$middleInitial}" : ''),
            $this->suffix_name
        ]);

        // Join with proper spacing/commas
        return implode(', ', [$nameParts[0], trim(implode(' ', array_slice($nameParts, 1)))]);
    }

    public function getCompleteNameAttribute(): string
    {
        // Build name parts array
        $nameParts = array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name, // full middle name
            $this->suffix_name
        ]);

        // Last name first, then the rest joined with spaces
        $lastName = array_shift($nameParts); // remove last_name from array
        $rest = implode(' ', $nameParts); // join first, middle, suffix

        return "{$lastName}, {$rest}";
    }
}
