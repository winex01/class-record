<?php

namespace App\Services;

use Filament\Support\Icons\Heroicon;

final class Icon
{
    public static function classes(): Heroicon|string
    {
        return Heroicon::RectangleGroup;
    }

    public static function students(): Heroicon|string
    {
        return Heroicon::Users;
    }

    public static function attendances(): Heroicon|string
    {
        return Heroicon::CalendarDays;
    }

    public static function myFiles(): Heroicon|string
    {
        return Heroicon::ClipboardDocument;
    }

    public static function assessments(): Heroicon|string
    {
        return Heroicon::ClipboardDocumentList;
    }

    public static function assessmentTypes(): Heroicon|string
    {
        return Heroicon::Square2Stack;
    }

    public static function groups(): Heroicon|string
    {
        return Heroicon::Cube;
    }
}
