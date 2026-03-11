<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Widgets\UpcomingAssessmentsWidget;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassActions;
use App\Filament\Resources\SchoolClasses\Forms\SchoolClassAssessmentForm;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassAssessmentActions;
use App\Filament\Resources\SchoolClasses\Filters\SchoolClassAssessmentFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassAssessmentColumns;

class ManageSchoolClassAssessments extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;
    protected static string $relationship = 'assessments';

    protected function getHeaderWidgets(): array
    {
        return [
            ...static::myWidgets($this->getOwnerRecord()),

            UpcomingAssessmentsWidget::make([
                'ownerRecord' => $this->getOwnerRecord(),
            ]),
        ];
    }

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
                SchoolClassAssessmentActions::recordScoreAction(),
                ViewAction::make(),
                EditAction::make()->after(fn () => $this->dispatch('refreshCollapsibleTableWidget')),
                DeleteAction::make()->after(fn () => $this->dispatch('refreshCollapsibleTableWidget')),
            ])
            ->toolbarActions([
                SchoolClassActions::createWithStudentsAction($this->getOwnerRecord())->label('New Assessment'),
                SchoolClassAssessmentActions::overviewAction(),
                DeleteBulkAction::make()->after(fn () => $this->dispatch('refreshCollapsibleTableWidget')),
            ])
            ->recordAction('recordScoreRelationManager');
    }
}
