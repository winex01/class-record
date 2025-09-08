<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssessmentType: string implements HasLabel
{
    case QUIZ = 'QUIZ';
    case TEST = 'TEST';
    case EXAM = 'EXAM';
    case ASSIGNMENT = 'ASSIGNMENT';
    case HOMEWORK = 'HOMEWORK';
    case PROJECT = 'PROJECT';
    case ORAL_RECITATION = 'ORAL_RECITATION';
    case PRESENTATION = 'PRESENTATION';
    case REPORTING = 'REPORTING';
    case THESIS = 'THESIS';
    case CAPSTONE = 'CAPSTONE';
    case RESEARCH = 'RESEARCH';
    case OTHERS = 'OTHERS'; // for custom or uncategorized types

    public function getLabel(): string
    {
        return match ($this) {
            self::QUIZ => 'Quiz',
            self::TEST => 'Test',
            self::EXAM => 'Exam',
            self::ASSIGNMENT => 'Assignment',
            self::HOMEWORK => 'Homework',
            self::PROJECT => 'Project',
            self::ORAL_RECITATION => 'Oral Recitation',
            self::PRESENTATION => 'Presentation',
            self::REPORTING => 'Reporting',
            self::THESIS => 'Thesis',
            self::CAPSTONE => 'Capstone',
            self::RESEARCH => 'Research',
            self::OTHERS => 'Other',
        };
    }
}
