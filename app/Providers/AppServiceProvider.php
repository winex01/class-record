<?php

namespace App\Providers;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachBulkAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Schemas\Components\Component;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::automaticallyEagerLoadRelationships();

        if (!app()->isProduction()) {
            Model::preventLazyLoading();
        }

        // align submit buttons of page to the right
        Page::formActionsAlignment(Alignment::Right);

        Component::configureUsing(function (Component $component): void {
            $component->columnSpanFull();
        });

        $this->tableConfig();
        $this->actionConfig();
        $this->fieldConfig();
    }

    public function tableConfig()
    {
        Table::configureUsing(function (Table $table): void {
            // Session persistence
            $table->persistFiltersInSession();
            $table->persistSortInSession();

            // Table styling
            $table->striped();
            $table->extremePaginationLinks();

            // Actions configuration
            $table->filtersTriggerAction(fn (Action $action) => $action->button()->label(__('Filters')));
            $table->toggleColumnsTriggerAction(fn (Action $action) => $action->button()->label(__('Columns')));
            $table->actionsAlignment('left');

            // Date/Time formatting
            $table->defaultDateDisplayFormat('M d, Y');          // e.g. "Sep 30, 2025"
            $table->defaultDateTimeDisplayFormat('M d, Y h:i A'); // e.g. "Sep 30, 2025 05:30 PM"

            // Initialize empty filters array
            $table->filters([]);
        });
    }

    public function actionConfig()
    {
        Action::configureUsing(function (Action $action)  {
            // Auto-refresh navigation after all successful actions
            $action->after(function (\Livewire\Component $livewire) {
                $livewire->dispatch('refresh-sidebar');
            });

            foreach ([
                DeleteAction::class,
                DeleteBulkAction::class,
                DetachAction::class,
                DetachBulkAction::class,
            ] as $class) {
                if ($action instanceof $class) {
                    // Skip alignment for these actions
                    return;
                }
            }
            // All other modal actions → align footer to the right
            $action->modalFooterActionsAlignment(Alignment::Right);
        });
    }

    public function fieldConfig()
    {
        // Add this separate configuration for Select components
        Select::configureUsing(function (Select $select) {
            $select
                ->createOptionAction(
                    fn (Action $action) => $action->modalWidth(Width::Medium)
                )
                ->editOptionAction(
                    fn (Action $action) => $action->modalWidth(Width::Medium)
                );
        });
    }
}
