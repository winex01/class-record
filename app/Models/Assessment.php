<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchoolClass;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use BelongsToSchoolClass;

    protected $guarded = [];

}
