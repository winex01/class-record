<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\AssessmentType;

class TenantSeeder
{
    public function seedTenantData(User $user)
    {
        $this->assessmentTypeSeeder($user);
        $this->groupSeeder($user);
    }

    public function groupSeeder($user)
    {
        $types = [
            ['name' => 'Group 1', 'user_id' => $user->id],
            ['name' => 'Group 2', 'user_id' => $user->id],
            ['name' => 'Group 3', 'user_id' => $user->id],
            ['name' => 'Group 4', 'user_id' => $user->id],
            ['name' => 'Group 5', 'user_id' => $user->id],
        ];

        foreach ($types as $type) {
            Group::create($type);
        }
    }

    public function assessmentTypeSeeder($user)
    {
        $types = [
            ['name' => 'Quiz', 'user_id' => $user->id],
            ['name' => 'Exam', 'user_id' => $user->id],
            ['name' => 'Assignment', 'user_id' => $user->id],
            ['name' => 'Project', 'user_id' => $user->id],
            ['name' => 'Recitation', 'user_id' => $user->id],
        ];

        foreach ($types as $type) {
            AssessmentType::create($type);
        }
    }
}
