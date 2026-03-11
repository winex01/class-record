<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
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

    // SCOPES
    public function scopeWithPaymentStatus($query, bool $completed = false)
    {
        $method = $completed ? 'whereDoesntHave' : 'whereHas';

        return $query->$method('students', function ($q) {
            $q->where(function ($sub) {
                $sub->where('fee_collections.amount', 0)
                    ->whereNull('fee_collection_student.amount');
            })->orWhere(function ($sub) {
                $sub->where('fee_collections.amount', '>', 0)
                    ->whereNull('fee_collection_student.amount')
                    ->orWhereColumn('fee_collection_student.amount', '<', 'fee_collections.amount');
            });
        });
    }

    // ACCESORS
    public function getIsVoluntaryAttribute(): bool
    {
        return $this->amount === 0.0;
    }

    public function getIsCompletedAttribute(): bool
    {
        if ($this->is_voluntary) {
            return !$this->students()->wherePivotNull('amount')->exists();
        }

        return !$this->students()
            ->withPivot(['amount'])
            ->get()
            ->contains(fn ($student) => ($student->pivot->amount ?? 0) < $this->amount);
    }
}
