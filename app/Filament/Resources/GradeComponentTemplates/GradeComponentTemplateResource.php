<?php

namespace App\Filament\Resources\GradeComponentTemplates;

use BackedEnum;
use App\Services\Icon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;
use App\Models\GradeComponentTemplate;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassGrades;
use App\Filament\Resources\GradeComponentTemplates\Pages\ManageGradeComponentTemplates;

class GradeComponentTemplateResource extends Resource
{
    protected static ?string $model = GradeComponentTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \UnitEnum | null $navigationGroup = \App\Enums\NavigationGroup::Group1;

    protected static ?int $navigationSort = 450;

    public static function getNavigationIcon(): string | BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return Icon::gradingComponents();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(static::getFields());
    }

    public static function getFields()
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->unique(
                    table: 'grade_component_templates',
                    modifyRuleUsing: function ($rule) {
                        return $rule->where('user_id', auth()->id());
                    }
                ),

            Repeater::make('components')
                ->hiddenLabel()
                ->collapsible()
                ->orderable()
                ->minItems(1)
                ->collapsed(fn ($operation) => $operation == 'view' ? true : false)
                ->itemLabel(fn (array $state): ?string =>
                    isset($state['name'], $state['weighted_score'])
                        ? "{$state['name']} ({$state['weighted_score']}%)"
                        : ($state['name'] ?? 'New Component')
                )
                ->schema(ManageSchoolClassGrades::getComponentFields())
                ->rules([
                    fn ($get) => function (string $attribute, $value, $fail) use ($get) {
                        $total = collect($get('components'))->sum('weighted_score');
                        if ($total != 100) {
                            $fail("The total weighted score of all components must equal 100%. Current total: {$total}%");
                        }
                    },
                ])
                ->addActionLabel('Add grading component')
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),

                TextColumn::make('components')
                    ->listWithLineBreaks()
                    ->formatStateUsing(fn ($state) =>
                        "{$state['name']} ({$state['weighted_score']}%)"
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->whereRaw(
                            "LOWER(JSON_EXTRACT(components, '$')) LIKE ?",
                            ['%' . strtolower($search) . '%']
                        );
                    })
                    ->color(function ($state, $rowLoop) {
                        return match ($rowLoop->iteration % 5) {
                            1 => 'primary',
                            2 => 'info',
                            3 => 'warning',
                            4 => 'pink',
                            default => 'purple',
                        };
                    })
            ])
            ->recordActions([
                ViewAction::make()->modalWidth(Width::ExtraLarge),
                EditAction::make()->modalWidth(Width::ExtraLarge),
                DeleteAction::make(),
            ])
            ->toolbarActions([
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
