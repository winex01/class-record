<?php
// app/Services/GradeCalculation.php

namespace App\Services;

use App\Models\SchoolClass;

class GradeCalculation
{
    public static function getTransmutedGrade(SchoolClass $schoolClass, float $rawGrade)
    {
        $transmutations = $schoolClass->gradeTransmutations;

        foreach ($transmutations as $transmutation) {
            if ($rawGrade >= $transmutation->initial_min &&
                $rawGrade <= $transmutation->initial_max) {
                return $transmutation->transmuted_grade;
            }
        }

        return $rawGrade;
        // return number_format(round($rawGrade, 2), 2);
    }
}
