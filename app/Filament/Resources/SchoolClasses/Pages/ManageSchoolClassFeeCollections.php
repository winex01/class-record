<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use App\Enums\FeeCollectionStatus;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\RelationManagers\TakeFeeCollectionRelationManager;

class ManageSchoolClassFeeCollections extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'feeCollections';

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->count()
                ),

            // TODO:: tab
            // 'Collected' => Tab::make()
            //     ->modifyQueryUsing(fn (Builder $query) => $query->where('is_collected', true))
            //     ->badgeColor('info')
            //     ->badge(fn () =>
            //         $this->getOwnerRecord()->{static::$relationship}()->where('is_collected', true)->count()
            //     ),

            // 'Uncollected' => Tab::make()
            //     ->modifyQueryUsing(fn (Builder $query) => $query->where('is_collected', false))
            //     ->badgeColor('danger')
            //     ->badge(fn () =>
            //         $this->getOwnerRecord()->{static::$relationship}()->where('is_collected', false)->count()
            //     )
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('amount')
                    ->default(0)
                    ->helperText('Fee amount.')
                    ->required()
                    ->numeric(),

                Field::date('date'),

                Textarea::make('description')
                        ->rows(2)
                        ->placeholder('Additional details...')
                        ->autosize(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Column::text('name'),
                Column::amount('amount')
                    ->color('info'),
                Column::date('date')
                    ->width('1%'),
                Column::text('description')
                    ->toggleable(isToggledHiddenByDefault: true),

                Column::amount('total')
                    ->state(fn ($record) => $record->students()->sum('amount'))
                    ->tooltip('Total collected'),

                Column::icon('status')
                    ->getStateUsing(fn ($record) =>
                        $record->students()
                            ->where('status', '!=', FeeCollectionStatus::PAID->value)
                            ->exists()
                    )
                    ->tooltip(function ($record) {
                        $hasUnpaid = $record->students()
                            ->where('status', '!=', FeeCollectionStatus::PAID->value)
                            ->exists();

                        return $hasUnpaid ? 'Not Collected' : 'Collected';
                    })
            ])
            ->filters([
                // TernaryFilter::make('is_collected')->label('Collected')
                // TODO:: status
            ])
            ->headerActions([
                SchoolClassResource::createAction($this->getOwnerRecord())->modalWidth(Width::Large),
            ])
            ->recordActions([
                ActionGroup::make([
                    RelationManagerAction::make('takeFeeCollectionRelationManager')
                        ->label('Take Fee')
                        ->icon(\App\Services\Icon::students())
                        ->color('info')
                        ->slideOver()
                        ->relationManager(TakeFeeCollectionRelationManager::make()),

                    ViewAction::make()->modalWidth(Width::Large),
                    EditAction::make()->modalWidth(Width::Large),
                    DeleteAction::make(),
                ])->grouped()
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordAction('takeFeeCollectionRelationManager');;
    }
}
