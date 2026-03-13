<?php

namespace App\Models;

use Illuminate\Support\Collection;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchoolClass;

class GradeTransmutation extends Model
{
    use BelongsToUser;
    use BelongsToSchoolClass;

    protected $guarded = [];
}
