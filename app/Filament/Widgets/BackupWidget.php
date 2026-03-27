<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Widgets\Widget;
use Filament\Facades\Filament;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;

class BackupWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.widgets.backup-widget';
    protected int|string|array $columnSpan = 'half';

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }

    public function backupAction(): Action
    {
        return Action::make('backup')
            ->label('Backup Now')
            ->icon('heroicon-o-archive-box-arrow-down')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Create Backup')
            ->modalDescription('This will create a full backup of your application. Continue?')
            ->modalSubmitActionLabel('Yes, Backup now!')
            ->modalFooterActionsAlignment(Alignment::Center)
            ->action(function () {
                try {
                    Artisan::call('backup:run');

                    // Get the latest backup file
                    $files = Storage::disk('local')->files(config('app.name'));

                    if (empty($files)) {
                        throw new \Exception('Backup file not found.');
                    }

                    $latest = collect($files)
                        ->sortByDesc(fn($f) => Storage::disk('local')->lastModified($f))
                        ->first();

                    Notification::make()
                        ->title('Backup Successful')
                        ->body('Download will start shortly.')
                        ->success()
                        ->send();

                    $this->js("window.location.href = '" . route('filament.app.backup.download', ['file' => basename($latest)]) . "'");

                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Backup Failed')
                        ->body('Error: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function getLastBackupTime(): string
    {
        try {
            $backupName = config('backup.backup.name');

            $files = Storage::disk('local')->files($backupName);

            if (empty($files)) {
                return 'No backups found';
            }

            $latest = collect($files)
                ->sortByDesc(fn($f) => Storage::disk('local')->lastModified($f))
                ->first();

            return Carbon::createFromTimestamp(
                Storage::disk('local')->lastModified($latest)
            )->diffForHumans();

        } catch (\Throwable $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
