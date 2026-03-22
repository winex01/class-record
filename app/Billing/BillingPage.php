<?php

namespace App\Billing;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;

class BillingPage extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected string $view = 'filament.resources.pages.billing';
    protected static bool $shouldRegisterNavigation = false;
    public string $app_id = '';

    public function mount(): void
    {
        $this->app_id = BillingService::getAppId(auth()->user());
    }

    public function activateAction(): Action
    {
        return Action::make('activate')
            ->label('Activate License')
            ->modalWidth(Width::Large)
            ->form([
                TextInput::make('app_id')
                    ->label('Your APP ID')
                    ->default(fn() => $this->app_id)
                    ->readOnly()
                    ->copyable(),
                FileUpload::make('license_file')
                    ->label('License File (.lic)')
                    ->maxSize(1024)
                    ->required(),
            ])
            ->modalSubmitActionLabel('Activate')
            ->action(function (array $data) {
                $user = auth()->user();
                $uploaded = storage_path('app/private/livewire-tmp/' . $data['license_file']);

                $result = BillingService::verifyLicenseFile($uploaded, $user);

                if (! $result['valid']) {
                    if (file_exists($uploaded)) {
                        unlink($uploaded);
                    }

                    Notification::make()
                        ->title('Invalid License!')
                        ->body($result['message'])
                        ->danger()
                        ->send();
                    return;
                }

                $destination = storage_path('licenses/' . $user->id . '_license.lic');
                rename($uploaded, $destination);

                License::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'file_path'  => $destination,
                        'app_id'     => $result['app_id'],
                        'signature'  => $result['signature'],
                        'expires_at' => $result['expires_at'],
                    ]
                );

                Notification::make()
                    ->title('License activated successfully!')
                    ->success()
                    ->send();

                $this->redirect(request()->header('Referer'));
            });
    }
}
