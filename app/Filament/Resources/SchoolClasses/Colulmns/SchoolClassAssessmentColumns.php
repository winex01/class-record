<?php

namespace App\Filament\Resources\SchoolClasses\Colulmns;

use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TextColumn;
use App\Enums\CompletedPendingStatus;
use App\Filament\Columns\BooleanColumn;
use App\Filament\Columns\BooleanIconColumn;

class SchoolClassAssessmentColumns
{
    public static function schema()
    {
        return [
            TextColumn::make('name'),

            TextColumn::make('assessmentType.name')
                ->label('Type')
                ->color('primary'),

            'max_score' =>
            TextColumn::make('max_score')
                ->label('Max')
                ->color('info')
                ->tooltip('Max score'),

            DateColumn::make('date'),

            TextColumn::make('description')
                ->toggleable(isToggledHiddenByDefault:true),


            TextColumn::make('myFile.path')
                ->label('File')
                ->toggleable(isToggledHiddenByDefault:true)
                ->html()
                ->state(fn ($record) => $record->myFile
                    ? collect($record->myFile->path)
                        ->map(fn ($path, $index) =>
                            '<a href="' .
                                route('filament.app.myfile.download', ['myFileId' => $record->myFile->id, 'index' => $index]) .
                            '" class="text-info-500 hover:text-info-600 hover:underline inline" target="_blank">' .
                            basename($path) . '</a>'
                        )
                        ->join('<span class="mx-1">, </span>')
                    : null
                )
                ->description(fn ($record) => $record->myFile->name),

            BooleanColumn::make('can_group_students')
                ->toggleable(isToggledHiddenByDefault:true)
                ->label('Can group'),

            'status' =>
            BooleanIconColumn::make('status')
                ->getStateUsing(fn ($record) =>
                    !$record->students()
                        ->whereNull('score')
                        ->exists()
                )
                ->tooltip(function ($record) {
                    $status = $record->students()
                        ->whereNull('score')
                        ->exists();

                    return $status ? CompletedPendingStatus::PENDING->getLabel() : CompletedPendingStatus::COMPLETED->getLabel();
                })
                ->sortable(
                    query: fn ($query, string $direction) =>
                        $query->withExists([
                            'students as has_pending' => fn ($q) => $q->whereNull('score')
                        ])
                        ->orderBy('has_pending', $direction)
                )
        ];
    }
}
