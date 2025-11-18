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

        return null; // or you could throw an exception if preferred
    }

    public static function getTransmutedGrades(SchoolClass $schoolClass, array $rawGrades): array
    {
        $results = [];
        foreach ($rawGrades as $rawGrade) {
            $results[$rawGrade] = $this->getTransmutedGrade($schoolClass, $rawGrade);
        }
        return $results;
    }
}
