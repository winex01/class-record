<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Actions\DetachBulkAction;
use App\Events\SchoolClassStudentsChanged;
use App\Filament\Widgets\RecentBirthdaysWidget;
use App\Filament\Widgets\UpcomingBirthdaysWidget;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Students\Schemas\StudentForm;
use App\Filament\Resources\Students\Filters\StudentFilters;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Filters\SchoolClassStudentFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassStudentColumns;

class ManageSchoolClassStudents extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;
    protected static string $relationship = 'students';

    protected function getHeaderWidgets(): array
    {
        return [
            ...static::myWidgets($this->getOwnerRecord()),

            UpcomingBirthdaysWidget::make([
                'ownerRecord' => $this->getOwnerRecord(),
            ]),

            RecentBirthdaysWidget::make([
                'ownerRecord' => $this->getOwnerRecord(),
            ]),
        ];
    }

    public function getTabs(): array
    {
        return SchoolClassStudentFilters::getTabs($this->getOwnerRecord());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(StudentForm::getFields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns(SchoolClassStudentColumns::schema(['photo', 'full_name', 'gender', 'birth_date', 'email']))
            ->filters([StudentFilters::gender()])
            ->recordActions([
                ViewAction::make()->modalWidth(Width::Large),
                EditAction::make()->modalWidth(Width::Large)
                    ->after(fn ($record) => $this->dispatch('refreshCollapsibleTableWidget')),
                DetachAction::make()->color('warning')
                    ->after(function ($record) {
                        event(new SchoolClassStudentsChanged(
                            $this->getOwnerRecord(),
                            [$record->id],
                            'detach')
                        );

                        $this->dispatch('refreshCollapsibleTableWidget');
                    }),
            ])
            ->toolbarActions([
                CreateAction::make()->label('New Student') ->modalWidth(Width::Large)
                    ->after(fn ($record) => $this->dispatch('refreshCollapsibleTableWidget')),

                Action::make('attachStudentsAction')
                    ->label('Attach Students')
                    ->color('info')
                    ->modalWidth(Width::Large)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalContent(fn ($livewire) => new HtmlString(
                        Blade::render(
                            '@livewire("attach-students", ["schoolClass" => $schoolClass])',
                            ['schoolClass' => $livewire->getOwnerRecord()]
                        )
                    )),

                DetachBulkAction::make()->color('warning')
                    ->after(fn ($record) => $this->dispatch('refreshCollapsibleTableWidget')),
            ]);
    }
}
