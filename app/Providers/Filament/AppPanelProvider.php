<?php

namespace App\Providers\Filament;

use Filament\Panel;
use App\Models\MyFile;
use Filament\PanelProvider;
use Filament\Enums\ThemeMode;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckStudentBirthdays;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('/')
            ->login()
            ->spa()
            ->defaultThemeMode(ThemeMode::Dark)
            ->colors([
                'primary' => Color::Emerald,
                'gray' => Color::Slate,
                'pink' => Color::Pink,
                'purple' => Color::Purple,
                'orange' => Color::Orange,
                'cyan' => Color::Cyan,
                'lime' => Color::Lime,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                CheckStudentBirthdays::class,
            ])
            ->viteTheme('resources/css/filament/app/theme.css')
            ->routes(function () {
                static::registerCustomRoutes();
            })
            ->databaseNotifications();
    }

    private static function registerCustomRoutes(): void
    {
        Route::get('/my-files/download/{myFileId}/{index}', function ($myFileId, $index) {
            $myFile = MyFile::findOrFail($myFileId);

            if (!isset($myFile->path[$index])) {
                abort(404);
            }

            $filePath = $myFile->path[$index];

            if (!Storage::disk('local')->exists($filePath)) {
                abort(404);
            }

            return Storage::disk('local')->download($filePath);
        })->name('myfile.download');

    }
}
