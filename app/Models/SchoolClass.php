<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolClass extends Model
{
    use HasFactory;
    use BelongsToUser;

    protected $guarded = [];

    protected $casts = [
        'year_section' => 'array',
        'date_start' => 'date',
        'date_end' => 'date',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class)->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function feeCollections()
    {
        return $this->hasMany(FeeCollection::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function gradingComponents()
    {
        return $this->hasMany(GradingComponent::class)
            ->orderBy('sort', 'asc');
    }

    public function gradeTransmutations()
    {
        return $this->hasMany(GradeTransmutation::class)
            ->orderBy('transmuted_grade', 'desc');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function getYearSectionFormattedAttribute(): string
    {
        return str_replace(',', ', ', $this->year_section ?? '');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->active ? 'Active' : 'Archived';
    }
}
