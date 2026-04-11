<?php

namespace App\Filament\Resources\SchoolClasses\Forms;

use App\Models\MyFile;
use Filament\Actions\Action;
use App\Filament\Fields\Select;
use App\Filament\Fields\Textarea;
use Filament\Support\Enums\Width;
use App\Filament\Fields\TextInput;
use App\Filament\Fields\DatePicker;
use App\Filament\Fields\ToggleButtons;
use Filament\Schemas\Components\Section;
use App\Filament\Resources\MyFiles\Forms\MyFileForm;
use App\Filament\Resources\AssessmentTypes\Schemas\AssessmentTypeForm;

class SchoolClassAssessmentForm
{
    public static function schema()
    {
        return [
            Section::make()
            ->schema([
                TextInput::make('name')
                    ->placeholder('e.g., Quiz #1, Midterm Exam, Chapter 5 Test, etc.')
                    ->required()
                    ->maxLength(255),

                Select::make('assessment_type_id')
                    ->relationship('assessmentType', 'name')
                    ->required()
                    ->createOptionForm(AssessmentTypeForm::getFields())
                    ->editOptionForm(AssessmentTypeForm::getFields()),

                TextInput::make('max_score')
                    ->helperText('Highest points')
                    ->required()
                    ->placeholder('100')
                    ->numeric(),

                DatePicker::make('date'),

                Textarea::make('description')
                    ->placeholder('Additional notes or instructions...')
            ])
            ->columnSpan(1),

        Section::make()
            ->schema([
                Select::make('my_file_id')
                    ->label('File')
                    ->hint('Attach related files')
                    ->relationship('myFile', 'name')
                    ->helperText('Optional')
                    ->nullable()
                    ->createOptionForm(MyFileForm::schema())
                    ->createOptionAction(
                        fn (Action $action) => $action->modalWidth(Width::Medium),
                    )
                    ->editOptionForm(MyFileForm::schema())
                    ->editOptionAction(function (Action $action) {
                        return $action
                            ->icon('heroicon-o-pencil')
                            ->tooltip('Edit')
                            ->modalWidth(Width::Medium);
                    })
                    ->suffixAction(function ($state): Action {
                        return Action::make('myFileView')
                            ->icon('heroicon-o-eye')
                            ->color(false)
                            ->tooltip('View')
                            ->modalWidth(Width::Medium)
                            ->modalHeading(fn($record) => 'View ' . $record->myFile->name)
                            ->schema(MyFileForm::schema())
                            ->fillForm(function () use ($state): array {
                                if (!$state) {
                                    return [];
                                }

                                return MyFile::findOrFail($state)->toArray();
                            })
                            ->disabledSchema()
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                            ->visible(fn () => filled($state));
                    }),

                ToggleButtons::make('can_group_students')
                    ->label('Grouping')
                    ->belowContent('Allow Student Grouping')
            ])
            ->columnSpan(1)
        ];
    }
}
