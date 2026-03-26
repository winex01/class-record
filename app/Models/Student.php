<?php

namespace App\Models;

use App\Enums\Gender;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;
    use BelongsToUser;

    protected $guarded = [];

    protected $casts = [
        'birth_date' => 'date',
        'gender' => Gender::class,
    ];

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
            ->withPivot(['amount']);
    }

    // SCOPE
    public function scopeBirthdayToday($query)
    {
        return $query->whereMonth('birth_date', now()->month)
            ->whereDay('birth_date', now()->day);
    }

    public function scopeUpcomingBirthdays($query, int $days = 10)
    {
        $driver = $query->getConnection()->getDriverName();
        $today = now()->format('m-d');
        $future = now()->addDays($days)->format('m-d');

        $dateExpr = $driver === 'sqlite'
            ? "strftime('%m-%d', birth_date)"
            : 'DATE_FORMAT(birth_date, "%m-%d")';

        return $query->where(function ($q) use ($dateExpr, $today, $future) {
            if ($today <= $future) {
                // Normal range e.g. Mar 22 → Apr 01
                $q->whereRaw("{$dateExpr} >= ?", [$today])
                    ->whereRaw("{$dateExpr} <= ?", [$future]);
            } else {
                // Year-end wrap e.g. Dec 28 → Jan 07
                $q->whereRaw("{$dateExpr} >= ?", [$today])
                    ->orWhereRaw("{$dateExpr} <= ?", [$future]);
            }
        })
            ->orderByRaw("{$dateExpr} ASC");
    }

    public function scopeRecentBirthdays($query, int $days = 10)
    {
        $driver = $query->getConnection()->getDriverName();
        $past = now()->subDays($days)->format('m-d');
        $yesterday = now()->subDay()->format('m-d');

        $dateExpr = $driver === 'sqlite'
            ? "strftime('%m-%d', birth_date)"
            : 'DATE_FORMAT(birth_date, "%m-%d")';

        return $query->where(function ($q) use ($dateExpr, $past, $yesterday) {
            if ($past <= $yesterday) {
                // Normal range
                $q->whereRaw("{$dateExpr} >= ?", [$past])
                    ->whereRaw("{$dateExpr} <= ?", [$yesterday]);
            } else {
                // Year-start wrap e.g. past = Dec 28, yesterday = Jan 06
                $q->whereRaw("{$dateExpr} >= ?", [$past])
                    ->orWhereRaw("{$dateExpr} <= ?", [$yesterday]);
            }
        })
            ->orderByRaw("{$dateExpr} DESC");
    }

    // ATTRIBUTES
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

    public function getInitialsAttribute(): string
    {
        $firstInitial = $this->first_name ? strtoupper(substr($this->first_name, 0, 1)) : '';
        $lastInitial = $this->last_name ? strtoupper(substr($this->last_name, 0, 1)) : '';

        return $lastInitial . $firstInitial;
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
