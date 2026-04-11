<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Enums\Gender;
use App\Filament\Fields\Select;
use App\Filament\Fields\TextInput;
use App\Filament\Fields\DatePicker;
use App\Filament\Fields\PhoneInput;
use Filament\Forms\Components\FileUpload;

class StudentForm
{
    public static function getFields()
    {
        return [
            FileUpload::make('photo')
                ->directory('student-photos')
                ->maxSize(10000) // 10 MB
                ->avatar(),

            TextInput::make('last_name')
                ->required()
                ->maxLength(255),

            TextInput::make('first_name')
                ->required()
                ->maxLength(255),

            TextInput::make('middle_name')
                ->maxLength(255),

            TextInput::make('suffix_name')
                ->placeholder('Jr. I, II')
                ->maxLength(255),

            Select::make('gender')
                ->searchable(false)
                ->enum(Gender::class)
                ->options(Gender::class)
                ->required(),

            DatePicker::make('birth_date'),

            TextInput::make('email')
                ->email(),

            PhoneInput::make('contact_number'),
        ];
    }
}
