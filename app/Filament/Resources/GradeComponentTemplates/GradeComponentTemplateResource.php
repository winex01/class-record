<?php

namespace App\Filament\Resources\GradeComponentTemplates;

use BackedEnum;
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
use App\Models\GradeComponentTemplate;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\GradeComponentTemplates\Forms\GradeComponentTemplateForm;
use App\Filament\Resources\GradeComponentTemplates\Pages\ManageGradeComponentTemplates;
use App\Filament\Resources\GradeComponentTemplates\Columns\GradeComponentTemplateColumns;

class GradeComponentTemplateResource extends Resource
{
    protected static ?string $model = GradeComponentTemplate::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static string | \UnitEnum | null $navigationGroup = NavigationGroup::Group1;
    protected static ?int $navigationSort = 450;

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedAdjustmentsHorizontal;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(GradeComponentTemplateForm::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(GradeComponentTemplateColumns::schema())
            ->recordActions([
                ViewAction::make()->modalWidth(Width::ExtraLarge),
                EditAction::make()->modalWidth(Width::ExtraLarge),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('New Template')
                    ->modalWidth(Width::ExtraLarge),

                DeleteBulkAction::make(),
            ])
            ->recordAction('edit');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGradeComponentTemplates::route('/'),
        ];
    }
}
