<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchoolClass;

class Assessment extends Model
{
    use BelongsToUser;
    use BelongsToSchoolClass;

    protected $guarded = [];

    protected $casts = [
        'can_group_students' => 'boolean',
    ];

    public function assessmentType()
    {
        return $this->belongsTo(AssessmentType::class);
    }

    public function myFile()
    {
        return $this->belongsTo(MyFile::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class)
            ->withTimestamps()
            ->withPivot(['score']);
    }
}
