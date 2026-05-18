<?php

namespace RobertBoes\FilamentPasskeys;

use RobertBoes\FilamentPasskeys\Http\Responses\FilamentPasskeyLoginResponse;
use Illuminate\Support\ServiceProvider;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse;

class FilamentPasskeysServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-passkeys.php',
            'filament-passkeys',
        );

        $this->app->singleton(FilamentPasskeysPlugin::class);

        $this->app->bind(PasskeyLoginResponse::class, FilamentPasskeyLoginResponse::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-passkeys');

        $this->publishes([
            __DIR__ . '/../config/filament-passkeys.php' => config_path('filament-passkeys.php'),
        ], 'filament-passkeys-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-passkeys'),
        ], 'filament-passkeys-views');
    }
}
