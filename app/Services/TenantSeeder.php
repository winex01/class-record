<?php

namespace App\Services;

use App\Models\AssessmentType;
use App\Models\User;

class TenantSeeder
{
    public function seedTenantData(User $user)
    {
        $types = [
            ['name' => 'Quiz', 'user_id' => $user->id],
            ['name' => 'Test', 'user_id' => $user->id],
            ['name' => 'Exam', 'user_id' => $user->id],
            ['name' => 'Homework', 'user_id' => $user->id],
            ['name' => 'Project', 'user_id' => $user->id],
            ['name' => 'Oral Recitation', 'user_id' => $user->id],
            ['name' => 'Reporting', 'user_id' => $user->id],
        ];

        foreach ($types as $type) {
            AssessmentType::create($type);
        }

    }
}
