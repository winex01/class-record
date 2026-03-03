<?php

namespace App\Services;

use Filament\Support\Icons\Heroicon;

final class Icon
{
    public static function classes(): Heroicon|string
    {
        return Heroicon::OutlinedRectangleGroup;
    }

    public static function students(): Heroicon|string
    {
        return Heroicon::OutlinedUser;
    }

    public static function attendances(): Heroicon|string
    {
        return Heroicon::OutlinedCalendarDays;
    }

    public static function myFiles(): Heroicon|string
    {
        return Heroicon::OutlinedClipboardDocument;
    }

    public static function assessments(): Heroicon|string
    {
        return Heroicon::OutlinedClipboardDocumentList;
    }

    public static function assessmentTypes(): Heroicon|string
    {
        return Heroicon::OutlinedSquare2Stack;
    }

    public static function groups(): Heroicon|string
    {
        return Heroicon::OutlinedCube;
    }

    public static function feeCollections(): Heroicon|string
    {
        return Heroicon::OutlinedCircleStack;
    }

    public static function events(): Heroicon|string
    {
        return Heroicon::OutlinedCalendarDateRange;
    }

    public static function tasks(): Heroicon|string
    {
        return Heroicon::OutlinedClipboardDocumentList;
    }

    public static function notes(): Heroicon|string
    {
        return Heroicon::OutlinedClipboard;
    }

    public static function recurrings(): Heroicon|string
    {
        return Heroicon::OutlinedQueueList;
    }

    public static function grades(): Heroicon|string
    {
        return Heroicon::OutlinedClipboardDocumentCheck;
    }

    public static function settings(): Heroicon|string
    {
        return Heroicon::OutlinedCog8Tooth;
    }

    public static function gradingComponents(): Heroicon|string
    {
        return Heroicon::OutlinedAdjustmentsHorizontal;
    }

    public static function transmutations(): Heroicon|string
    {
        return Heroicon::OutlinedScale;
    }

    public static function lessons(): Heroicon|string
    {
        return Heroicon::OutlinedViewColumns;
    }
}
