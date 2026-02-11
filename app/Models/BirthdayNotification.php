<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class BirthdayNotification extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'student_id',
        'notification_date',
    ];

    protected $casts = [
        'notification_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
