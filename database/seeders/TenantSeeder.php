<?php

namespace Database\Seeders;

use App\Models\TransmuteTemplate;
use App\Models\User;
use App\Models\Group;
use App\Models\AssessmentType;

class TenantSeeder
{
    public function seedTenantData(User $user)
    {
        $this->assessmentTypeSeeder($user);
        $this->groupSeeder($user);
        $this->transmuteTemplatesSeeder($user);
    }

    private function transmuteTemplatesSeeder($user)
    {
        $item = TransmuteTemplate::create([
            'user_id' => $user->id,
            'name' => 'DepEd Transmutation',
        ]);

        $ranges = [
            ['initial_min' => '100.00', 'initial_max' => '100.00', 'transmuted_grade' => '100'],
            ['initial_min' => '98.40', 'initial_max' => '99.99', 'transmuted_grade' => '99'],
            ['initial_min' => '96.80', 'initial_max' => '98.39', 'transmuted_grade' => '98'],
            ['initial_min' => '95.20', 'initial_max' => '96.79', 'transmuted_grade' => '97'],
            ['initial_min' => '93.60', 'initial_max' => '95.19', 'transmuted_grade' => '96'],
            ['initial_min' => '92.00', 'initial_max' => '93.59', 'transmuted_grade' => '95'],
            ['initial_min' => '90.40', 'initial_max' => '91.99', 'transmuted_grade' => '94'],
            ['initial_min' => '88.80', 'initial_max' => '90.39', 'transmuted_grade' => '93'],
            ['initial_min' => '87.20', 'initial_max' => '88.79', 'transmuted_grade' => '92'],
            ['initial_min' => '85.60', 'initial_max' => '87.19', 'transmuted_grade' => '91'],
            ['initial_min' => '84.00', 'initial_max' => '85.59', 'transmuted_grade' => '90'],
            ['initial_min' => '82.40', 'initial_max' => '83.99', 'transmuted_grade' => '89'],
            ['initial_min' => '80.80', 'initial_max' => '82.39', 'transmuted_grade' => '88'],
            ['initial_min' => '79.20', 'initial_max' => '80.79', 'transmuted_grade' => '87'],
            ['initial_min' => '77.60', 'initial_max' => '79.19', 'transmuted_grade' => '86'],
            ['initial_min' => '76.00', 'initial_max' => '77.59', 'transmuted_grade' => '85'],
            ['initial_min' => '74.40', 'initial_max' => '75.99', 'transmuted_grade' => '84'],
            ['initial_min' => '72.80', 'initial_max' => '74.39', 'transmuted_grade' => '83'],
            ['initial_min' => '71.20', 'initial_max' => '72.79', 'transmuted_grade' => '82'],
            ['initial_min' => '69.60', 'initial_max' => '71.19', 'transmuted_grade' => '81'],
            ['initial_min' => '68.00', 'initial_max' => '69.59', 'transmuted_grade' => '80'],
            ['initial_min' => '66.40', 'initial_max' => '67.99', 'transmuted_grade' => '79'],
            ['initial_min' => '64.80', 'initial_max' => '66.39', 'transmuted_grade' => '78'],
            ['initial_min' => '63.20', 'initial_max' => '64.79', 'transmuted_grade' => '77'],
            ['initial_min' => '61.60', 'initial_max' => '63.19', 'transmuted_grade' => '76'],
            ['initial_min' => '60.00', 'initial_max' => '61.59', 'transmuted_grade' => '75'],
            ['initial_min' => '56.00', 'initial_max' => '59.99', 'transmuted_grade' => '74'],
            ['initial_min' => '52.00', 'initial_max' => '55.99', 'transmuted_grade' => '73'],
            ['initial_min' => '48.00', 'initial_max' => '51.99', 'transmuted_grade' => '72'],
            ['initial_min' => '44.00', 'initial_max' => '47.99', 'transmuted_grade' => '71'],
            ['initial_min' => '40.00', 'initial_max' => '43.99', 'transmuted_grade' => '70'],
            ['initial_min' => '36.00', 'initial_max' => '39.99', 'transmuted_grade' => '69'],
            ['initial_min' => '32.00', 'initial_max' => '35.99', 'transmuted_grade' => '68'],
            ['initial_min' => '28.00', 'initial_max' => '31.99', 'transmuted_grade' => '67'],
            ['initial_min' => '24.00', 'initial_max' => '27.99', 'transmuted_grade' => '66'],
            ['initial_min' => '20.00', 'initial_max' => '23.99', 'transmuted_grade' => '65'],
            ['initial_min' => '16.00', 'initial_max' => '19.99', 'transmuted_grade' => '64'],
            ['initial_min' => '12.00', 'initial_max' => '15.99', 'transmuted_grade' => '63'],
            ['initial_min' => '8.00', 'initial_max' => '11.99', 'transmuted_grade' => '62'],
            ['initial_min' => '4.00', 'initial_max' => '7.99', 'transmuted_grade' => '61'],
            ['initial_min' => '0.00', 'initial_max' => '3.99', 'transmuted_grade' => '60'],
        ];

        // Add user_id to each range and create them
        foreach ($ranges as $range) {
            $item->transmuteTemplateRanges()->create(array_merge($range, [
                'user_id' => $user->id
            ]));
        }
    }

    private function groupSeeder($user)
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

    private function assessmentTypeSeeder($user)
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
