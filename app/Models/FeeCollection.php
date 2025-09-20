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
}
