<?php

namespace App\Filament\Resources\SchoolClasses\Schemas;

use Illuminate\Support\Carbon;
use App\Filament\Fields\Textarea;
use App\Filament\Fields\TagsInput;
use App\Filament\Fields\TextInput;
use App\Filament\Fields\DatePicker;
use App\Filament\Fields\ToggleButtons;

class SchoolClassForm
{
    public static function getFields()
    {
        return [
            'name' =>
            TextInput::make('name')
                    ->label('Subject')
                    ->placeholder('e.g. Math 101 or ENG-201')
                    ->required()
                    ->maxLength(255),

            'year_section' =>
            TagsInput::make('year_section')
                ->placeholder('e.g. 1st Year, Grade 1, Section A'),

            'date_start' =>
            DatePicker::make('date_start')
                ->label('Start Date')
                ->placeholder('e.g. ' . Carbon::now()->format('M j, Y')), // e.g. Aug 28, 2025

            'date_end' =>
            DatePicker::make('date_end')
                ->label('End Date')
                ->placeholder('e.g. ' . Carbon::now()->addMonths(6)->format('M j, Y')), // e.g. Nov 28, 2025

            'description' =>
            Textarea::make('description')
                ->placeholder('Brief details about this subject... (optional)'),

            ToggleButtons::make('active')
                ->label('Status')
                ->helperText('Active records can be edited. Archived records are view-only.')
                ->icons([true => 'heroicon-o-check', false => 'heroicon-o-lock-closed'])
                ->colors([true => 'success', false => 'warning'])
                ->default(true)
                ->options([
                    true => 'Active',
                    false => 'Archived',
                ])
        ];
    }
}
