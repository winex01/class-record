<?php

namespace App\Filament\Actions;

use Throwable;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;

class BackupAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'backup';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Backup Now')
            ->icon('heroicon-o-archive-box-arrow-down')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Create Backup')
            ->modalDescription('This will create a full backup of your application (database + files). Continue?')
            ->modalSubmitActionLabel('Yes, backup now')
            ->action(function () {
                try {
                    Artisan::call('backup:run');

                    Notification::make()
                        ->title('Backup Successful')
                        ->body('Your application has been backed up successfully.')
                        ->success()
                        ->send();
                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Backup Failed')
                        ->body('Error: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
