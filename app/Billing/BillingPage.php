<?php

namespace App\Billing;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TextColumn;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Contracts\HasActions;
use Illuminate\Support\Facades\RateLimiter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;

class BillingPage extends Page implements HasForms, HasActions, HasTable
{
    use InteractsWithForms;
    use InteractsWithActions;
    use InteractsWithTable;

    protected string $view = 'filament.resources.pages.billing';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'License';
    public string $app_id = '';

    public function mount(): void
    {
        $this->app_id = BillingService::getAppId(auth()->user());

        if (!BillingService::isSubscribed(auth()->user())) {
            $this->mountAction('activate');
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(License::query())
            ->columns([
                TextColumn::make('app_id')
                    ->label('APP ID')
                    ->copyable(),

                DateColumn::make('expires_at')
                    ->label('Expires At')
                    ->sortable()
                    ->color(function ($record) {
                        if (!$record->expires_at) {
                            return 'gray';
                        }

                        $days = now()->diffInDays($record->expires_at, false);

                        if ($days < 0) {
                            return 'danger'; // already expired
                        }

                        if ($days < 2) {
                            return 'warning';
                        }

                        if ($days <= 30) {
                            return 'info';
                        }

                        return 'primary';
                    }),

                DateColumn::make('created_at')->label('Activated At')->sortable()
            ])
            ->defaultSort('expires_at', 'desc')
            ->toolbarActions([
                $this->activateAction()
            ]);
    }

    public function activateAction(): Action
    {
        return Action::make('activate')
            ->label('Activate License')
            ->modalWidth(Width::Large)
            ->form([
                TextInput::make('app_id')
                    ->label('APP ID')
                    ->default(fn() => $this->app_id)
                    ->readOnly()
                    ->copyable()
                    ->belowContent('Copy your APP ID and send it to the admin to get your license file'),
                FileUpload::make('license_file')
                    ->label('License File (.lic)')
                    ->directory('licenses')
                    ->maxSize(10)
                    ->required(),
            ])
            ->modalSubmitActionLabel('Activate')
            ->action(function (array $data) {
                $user = auth()->user();
                $key = 'activate-license:' . $user->id;

                if (RateLimiter::tooManyAttempts($key, 5)) {
                    $seconds = RateLimiter::availableIn($key);

                    return Notification::make()
                        ->title('Too many attempts!')
                        ->body("Please wait {$seconds} seconds before trying again.")
                        ->danger()
                        ->send();
                }

                RateLimiter::hit($key, 60);

                $relativePath = $data['license_file'];
                $uploaded = storage_path('app/private/' . $relativePath);

                $result = BillingService::verifyLicenseFile($uploaded, $user);

                if (!$result['valid']) {
                    if (file_exists($uploaded)) {
                        unlink($uploaded);
                    }

                    return Notification::make()
                        ->title('Invalid License!')
                        ->body($result['message'])
                        ->danger()
                        ->send();
                }

                $existing = License::where('signature', $result['signature'])->first();

                if ($existing) {
                    if (file_exists($uploaded)) {
                        unlink($uploaded);
                    }

                    return Notification::make()
                        ->title('License already activated!')
                        ->warning()
                        ->send();
                }

                License::create([
                    'user_id' => $user->id,
                    'file_path' => $relativePath,
                    'app_id' => $result['app_id'],
                    'signature' => $result['signature'],
                    'expires_at' => $result['expires_at'],
                ]);

                Notification::make()
                    ->title('License activated successfully!')
                    ->success()
                    ->send();
            })
            ->after(function () {
                $path = storage_path('app/private/licenses/');

                if (!is_dir($path))
                    return;

                foreach (glob($path . '*') as $file) {
                    if (is_file($file) && !str_ends_with($file, '.lic')) {
                        unlink($file);
                    }
                }
            });
    }
}
