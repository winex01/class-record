<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class AssessmentType extends Model
{
    use BelongsToUser;

    protected $guarded = [];

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
