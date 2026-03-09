<?php

namespace App\Events;

use App\Models\SchoolClass;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SchoolClassStudentsChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SchoolClass $schoolClass,
        public array $studentIds,
        public string $action, // 'attach' or 'detach'
    ) {}
}
