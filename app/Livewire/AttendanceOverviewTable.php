<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Student;

class AttendanceOverviewTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public $schoolClassId;
    public $studentsData = [];

    public function mount($schoolClassId, $studentsData)
    {
        $this->schoolClassId = $schoolClassId;
        $this->studentsData = $studentsData;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Student::query()->whereIn('id', array_column($this->studentsData, 'id')))
            ->columns([
                TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable(query: function ($query, $search) {
                        return $query->where(function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%")
                                ->orWhere('suffix_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                TextColumn::make('present_count')
                    ->label('Present')
                    ->state(function ($record) {
                        $studentData = collect($this->studentsData)->firstWhere('id', $record->id);
                        return $studentData['present_count'] ?? 0;
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('absent_count')
                    ->label('Absent')
                    ->state(function ($record) {
                        $studentData = collect($this->studentsData)->firstWhere('id', $record->id);
                        return $studentData['absent_count'] ?? 0;
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('danger')
                    ->sortable(),
            ])
            ->paginated([10, 25, 50]);
    }

    public function render()
    {
        return view('livewire.attendance-overview-table');
    }
}
