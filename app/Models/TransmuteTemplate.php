<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class TransmuteTemplate extends Model
{
    use BelongsToUser;

    protected $guarded = [];

    public function transmuteTemplateRanges()
    {
        return $this->hasMany(TransmuteTemplateRange::class)
            ->orderBy('transmuted_grade', 'desc');
    }
}
