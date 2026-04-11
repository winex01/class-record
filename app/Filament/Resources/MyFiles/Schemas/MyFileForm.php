<?php

namespace App\Filament\Resources\MyFiles\Schemas;

use App\Filament\Fields\TagsInput;
use App\Filament\Fields\TextInput;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MyFileForm
{
    public static function getFields()
    {
        return [
            TextInput::make('name')->required(),
            TagsInput::make('tags')->placeholder('e.g. Lesson, Assessment'),
            FileUpload::make('path')
                ->label('Files')
                ->required()
                ->multiple()
                ->directory(fn () => 'my-files/' . auth()->id())
                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $directory = 'my-files/' . auth()->id();

                    $fileName = "{$originalName}.{$extension}";
                    $counter = 1;

                    while (Storage::disk(config('filament.default_filesystem_disk'))->exists("{$directory}/{$fileName}")) {
                        $fileName = "{$originalName}-{$counter}.{$extension}";
                        $counter++;
                    }

                    return $fileName;
                })
                ->downloadable()
                ->openable()
                ->columns(12)
                ->deletable(fn ($operation) => $operation !== 'view')
                ->placeholder(fn ($operation) => $operation === 'view'
                    ? '<strong>Click on the icon to download or view</strong>'
                    : null)
                ->hint('Attach one or more files')
        ];
    }
}
