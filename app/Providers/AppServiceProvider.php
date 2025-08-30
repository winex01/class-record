<?php

namespace App\Providers;

use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\Alignment;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

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

        // align submit buttons of modal to the right
        Action::configureUsing(function (Action $action)  {
            foreach ([
                DeleteAction::class,
                DeleteBulkAction::class,
                // TODO::
                // DetachAction::class,
                // DetachBulkAction::class,
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
    }
}
