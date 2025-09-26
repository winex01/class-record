<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use BackedEnum;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Services\SelectOption;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\RelationManagers\TakeFeeCollectionRelationManager;

class ManageSchoolClassFeeCollections extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'feeCollections';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->count()
                ),

            'Collected' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_collected', true))
                ->badgeColor('info')
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->where('is_collected', true)->count()
                ),

            'Uncollected' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_collected', false))
                ->badgeColor('danger')
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->where('is_collected', false)->count()
                )
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('amount')
                        ->default(0)
                        ->helperText('Fee amount.')
                        ->required()
                        ->numeric(),

                    Field::date('date'),

                ])->columnSpan(1),

                Section::make()->schema([

                    Textarea::make('description')
                        ->rows(2)
                        ->placeholder('Additional details...')
                        ->autosize(),

                    ToggleButtons::make('is_collected')
                        ->label('Collected')
                        ->helperText('Marks the fee collection as completed once done')
                        ->inline()
                        ->default(false)
                        ->boolean(),

                ])->columnSpan(1),

            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Column::text('name'),
                Column::amount('amount')
                    ->color('info'),
                Column::text('date')
                    ->width('1%'),
                Column::text('description')
                    ->toggleable(isToggledHiddenByDefault: true),

                Column::select('is_collected')
                    ->label('Collected')
                    ->extraAttributes(['style' => 'min-width: 10px; '])
                    ->options(SelectOption::yesOrNo()),

                Column::amount('total')
                    ->state(fn ($record) => $record->students()->sum('amount'))
                    ->tooltip('Total collected'),
            ])
            ->filters([
                TernaryFilter::make('is_collected')->label('Collected')
            ])
            ->headerActions([
                SchoolClassResource::createAction($this->getOwnerRecord()),
            ])
            ->recordActions([
                ActionGroup::make([
                    RelationManagerAction::make('takeFeeCollectionRelationManager')
                        ->label('Take Fee')
                        ->icon(\App\Services\Icon::students())
                        ->color('info')
                        ->slideOver()
                        ->relationManager(TakeFeeCollectionRelationManager::make()),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])->grouped()
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordAction('takeFeeCollectionRelationManager');;
    }
}
