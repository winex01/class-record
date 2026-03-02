<?php

namespace App\Livewire;

use App\Models\MyFile;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\SchoolClass;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use App\Livewire\Traits\RenderTableTrait;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Resources\MyFiles\MyFileResource;
use Filament\Actions\Concerns\InteractsWithActions;

class LessonsAttachedFiles extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use RenderTableTrait;

    public int $schoolClassId;

    public function mount(int $schoolClassId): void
    {
        $this->schoolClassId = $schoolClassId;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $lessonIds = SchoolClass::find($this->schoolClassId)
            ->lessons
            ->pluck('id')
            ->toArray();

        return $table
            ->query(
                MyFile::query()
                    ->whereHas('lessons', fn (Builder $query) => $query->whereIn('lessons.id', $lessonIds))
            )
            ->columns([
                ...MyFileResource::getColumns(),
            ])
            ->recordActions([
                MyFileResource::getViewAction()->form(MyFileResource::getForm())->modalCancelAction(false)
            ])
            ->paginated([10, 25, 50])
            ->emptyStateHeading('No Records')
            ->emptyStateDescription('No attached files found.');
    }
}
