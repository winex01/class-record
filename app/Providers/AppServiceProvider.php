<?php

namespace App\Providers;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
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

        // Configure default options for table filters
        Table::configureUsing(function (Table $table): void {
            $table->persistFiltersInSession();
            $table->striped();
            $table->extremePaginationLinks();
            $table->filtersTriggerAction(fn (Action $action) => $action->button()->label(__('Filters')));
            $table->toggleColumnsTriggerAction(fn (Action $action) => $action->button()->label(__('Columns')));
            $table->persistSortInSession();
            $table->filters([]);
            $table->actionsAlignment('left');
        });

        Component::configureUsing(function (Component $component): void {
            $component->columnSpanFull();
        });

        Table::configureUsing(function (Table $table): void {
        // for date (no time)
        $table->defaultDateDisplayFormat('M d, Y');          // e.g. “Sep 30, 2025”
        // for datetime (with time)
        $table->defaultDateTimeDisplayFormat('M d, Y h:i A'); // e.g. “Sep 30, 2025 05:30 PM”
    });
    }
}
