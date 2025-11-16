<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class TransmuteTemplateRange extends Model
{
    use BelongsToUser;

    protected $guarded = [];

    public function transmuteTemplate()
    {
        return $this->belongsTo(TransmuteTemplate::class);
    }
}
