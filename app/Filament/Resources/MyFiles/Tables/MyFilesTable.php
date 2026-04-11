<?php

namespace App\Filament\Resources\MyFiles\Tables;

use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;

class MyFilesTable
{
    public static function getColumns()
    {
        return [
            TextColumn::make('name'),
            TagsColumn::make('tags'),

            TextColumn::make('path')
                ->label('Files')
                ->html()
                ->getStateUsing(fn ($record) => collect($record->path)
                    ->map(fn ($path, $index) =>
                        '<a href="' .
                            route('filament.app.myfile.download', ['myFileId' => $record->id, 'index' => $index]) . '" class="text-info-500 hover:text-info-600 hover:underline inline">' . basename($path)
                        . '</a>'
                    )
                    ->join('<span class="mx-1">, </span>')
                )
        ];
    }
}
