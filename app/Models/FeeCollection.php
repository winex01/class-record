<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchoolClass;

class FeeCollection extends Model
{
    use BelongsToUser;
    use BelongsToSchoolClass;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'float',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class)
            ->withTimestamps()
            ->withPivot(['amount']);
    }

    // ACCESORS
    public function getIsOpenContributionAttribute(): bool
    {
        return $this->amount === 0.0;
    }

    public function getIsCompletedAttribute(): bool
    {
        if ($this->is_open_contribution) {
            return !$this->students()->wherePivotNull('amount')->exists();
        }

        return !$this->students()
            ->withPivot(['amount'])
            ->get()
            ->contains(fn ($student) => ($student->pivot->amount ?? 0) < $this->amount);
    }
}
