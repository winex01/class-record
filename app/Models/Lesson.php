<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchoolClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;
    use BelongsToUser;
    use BelongsToSchoolClass;

    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'checklist' => 'array',
    ];

    public function myFiles()
    {
        return $this->belongsToMany(MyFile::class);
    }
}
