<?php

namespace RobertBoes\FilamentPasskeys;

use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Panel;
use RobertBoes\FilamentPasskeys\Filament\Pages\ManagePasskeys;

class FilamentPasskeysPlugin implements Plugin
{
    protected ?string $loginRenderHook = null;

    protected ?string $loginButtonLabel = null;

    protected bool $injectLoginButton = true;

    protected bool $registerManagePage = true;

    protected bool $registerUserMenuItem = true;

    protected ?string $userMenuItemLabel = null;

    public function getId(): string
    {
        return 'filament-passkeys';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function loginRenderHook(string $hook): static
    {
        $this->loginRenderHook = $hook;

        return $this;
    }

    public function loginButtonLabel(string $label): static
    {
        $this->loginButtonLabel = $label;

        return $this;
    }

    public function withoutLoginButton(): static
    {
        $this->injectLoginButton = false;

        return $this;
    }

    public function withoutManagePage(): static
    {
        $this->registerManagePage = false;

        return $this;
    }

    public function withoutUserMenuItem(): static
    {
        $this->registerUserMenuItem = false;

        return $this;
    }

    public function userMenuItemLabel(string $label): static
    {
        $this->userMenuItemLabel = $label;

        return $this;
    }

    public function getLoginButtonLabel(): string
    {
        return $this->loginButtonLabel
            ?? config('filament-passkeys.login_button_label', 'Sign in with passkey');
    }

    public function getUserMenuItemLabel(): string
    {
        return $this->userMenuItemLabel
            ?? config('filament-passkeys.user_menu_item_label', 'Passkeys');
    }

    public function register(Panel $panel): void
    {
        if ($this->injectLoginButton) {
            $hook = $this->loginRenderHook
                ?? config('filament-passkeys.login_render_hook');

            $panel->renderHook(
                $hook,
                fn (): string => view('filament-passkeys::login-button', [
                    'label' => $this->getLoginButtonLabel(),
                ])->render(),
            );
        }

        if ($this->registerManagePage) {
            $panel
                ->authenticatedRoutes(fn (Panel $panel) => ManagePasskeys::registerRoutes($panel))
                ->livewireComponents([
                    ManagePasskeys::class,
                ]);
        }

        if ($this->registerManagePage && $this->registerUserMenuItem) {
            $panel->userMenuItems([
                Action::make('passkeys')
                    ->label($this->getUserMenuItemLabel())
                    ->icon('heroicon-o-key')
                    ->url(fn (): string => ManagePasskeys::getUrl()),
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
