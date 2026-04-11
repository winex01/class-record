<?php

namespace App\Filament\Resources\Meetings;

use App\Models\Meeting;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\Meetings\Schemas\MeetingForm;
use App\Filament\Resources\Meetings\Pages\ManageMeetings;
use App\Filament\Resources\Meetings\Tables\MeetingsTable;

class MeetingResource extends Resource
{
    protected static ?string $model = Meeting::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static string | \UnitEnum | null $navigationGroup = NavigationGroup::Group2;
    protected static ?int $navigationSort = 280;

    public static function getNavigationIcon(): string | \BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedCalendarDateRange;
    }

    public static function getNavigationBadge(): ?string
    {
        return '◉';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'purple';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(MeetingForm::getFields());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(MeetingsTable::getColumns())
            ->recordActions([
                ViewAction::make()->modalWidth(Width::Medium),
                EditAction::make()->modalWidth(Width::Medium),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('New Meeting')
                    ->modalWidth(Width::Medium),

                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMeetings::route('/'),
        ];
    }
}
