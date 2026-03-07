<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassActions;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\Forms\SchoolClassAssessmentForm;
use App\Filament\Resources\SchoolClasses\Filters\SchoolClassAssessmentFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassAssessmentColumns;
use App\Filament\Resources\SchoolClasses\RelationManagers\RecordScoreRelationManager;

class ManageSchoolClassAssessments extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;
    protected static string $relationship = 'assessments';

    public function getTabs(): array
    {
        return SchoolClassAssessmentFilters::getTabs($this->getOwnerRecord());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(SchoolClassAssessmentForm::schema())
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns(SchoolClassAssessmentColumns::schema())
            ->filters([SchoolClassAssessmentFilters::types()])
            ->recordActions([
                RelationManagerAction::make('recordScoreRelationManager')
                    ->label('Score')
                    ->icon(StudentResource::getNavigationIcon())
                    ->color('info')
                    ->slideOver()
                    ->relationManager(RecordScoreRelationManager::make())
                    ->modalDescription(fn ($record) => new HtmlString(
                        view('filament.components.assessment-modal-heading', [
                            'record' => $record,
                        ])->render()
                    )),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                SchoolClassActions::createWithStudentsAction($this->getOwnerRecord())
                    ->label('New Assessment'),

                Action::make('overview')
                    ->color('info')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalWidth(Width::TwoExtraLarge)
                    ->modalHeading('Student Assessment Overview')
                    ->modalDescription(function ($livewire) {
                        return 'Overview of students across all assessment records for ' . $livewire->getOwnerRecord()->name;
                    })
                    ->modalContent(fn ($livewire) => new HtmlString(
                        Blade::render(
                            '@livewire("assessment-overview", ["schoolClassId" => $schoolClassId])',
                            ['schoolClassId' => $livewire->getOwnerRecord()->id]
                        )
                    )),

                DeleteBulkAction::make(),
            ])
            ->recordAction('recordScoreRelationManager');
    }
}
