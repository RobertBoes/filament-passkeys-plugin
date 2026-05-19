<?php

declare(strict_types=1);

namespace RobertBoes\FilamentPasskeys;

use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Laravel\Passkeys\Contracts\PasskeyUser;
use RobertBoes\FilamentPasskeys\Filament\Pages\ManagePasskeys;
use RobertBoes\FilamentPasskeys\Http\Controllers\ConfirmPasswordController;
use RobertBoes\FilamentPasskeys\Http\Controllers\ConfirmedPasswordStatusController;

class FilamentPasskeysPlugin implements Plugin
{
    protected ?string $loginRenderHook = null;

    protected ?string $loginButtonLabel = null;

    protected bool $registerLoginButton = true;

    protected bool $registerManagePage = false;

    protected bool $registerUserMenuItem = true;

    protected bool $registerProfilePageSection = true;

    protected ?string $userMenuItemLabel = null;

    public function getId(): string
    {
        return 'filament-passkeys';
    }

    public static function make(): static
    {
        /** @var static $instance */
        $instance = app(static::class);

        return $instance;
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(static::make()->getId());

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

    public function withLoginButton(bool $condition = true): static
    {
        $this->registerLoginButton = $condition;

        return $this;
    }

    public function withoutLoginButton(): static
    {
        return $this->withLoginButton(false);
    }

    public function withManagePage(bool $condition = true): static
    {
        $this->registerManagePage = $condition;

        return $this;
    }

    public function withoutManagePage(): static
    {
        return $this->withManagePage(false);
    }

    public function withUserMenuItem(bool $condition = true): static
    {
        $this->registerUserMenuItem = $condition;

        return $this;
    }

    public function withoutUserMenuItem(): static
    {
        return $this->withUserMenuItem(false);
    }

    public function withProfilePageSection(bool $condition = true): static
    {
        $this->registerProfilePageSection = $condition;

        return $this;
    }

    public function withoutProfilePageSection(): static
    {
        return $this->withProfilePageSection(false);
    }

    public static function passkeysSection(): Section
    {
        return Section::make('passkeys')
            ->label(__('filament-passkeys::passkeys.section.heading'))
            ->description(__('filament-passkeys::passkeys.section.description'))
            ->icon('heroicon-o-key')
            ->compact()
            ->divided()
            ->secondary()
            ->schema([
                View::make('filament-passkeys::passkey-manager')
                    ->viewData(fn (): array => [
                        'passkeys' => static::resolveCurrentUserPasskeys(),
                    ]),
            ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Laravel\Passkeys\Passkey>
     */
    protected static function resolveCurrentUserPasskeys(): \Illuminate\Support\Collection
    {
        $user = Filament::auth()->user();

        return $user instanceof PasskeyUser
            ? $user->passkeys()->latest()->get()
            : collect();
    }

    public function userMenuItemLabel(string $label): static
    {
        $this->userMenuItemLabel = $label;

        return $this;
    }

    public function getLoginButtonLabel(): string
    {
        return $this->loginButtonLabel
            ?: Config::string('filament-passkeys.login_button_label', '')
            ?: __('filament-passkeys::passkeys.login.button');
    }

    public function getUserMenuItemLabel(): string
    {
        return $this->userMenuItemLabel
            ?: Config::string('filament-passkeys.user_menu_item_label', '')
            ?: __('filament-passkeys::passkeys.menu.label');
    }

    public function register(Panel $panel): void
    {
        if ($this->registerLoginButton) {
            $hook = $this->loginRenderHook
                ?? Config::string('filament-passkeys.login_render_hook');

            $panel->renderHook(
                $hook,
                fn (): string => view('filament-passkeys::login-button', [
                    'label' => $this->getLoginButtonLabel(),
                ])->render(),
            );
        }

        $panel->authenticatedRoutes(function (Panel $panel): void {
            Route::post('filament-passkeys/confirm-password', ConfirmPasswordController::class)
                ->name('filament-passkeys.confirm-password.store');

            Route::get('filament-passkeys/confirm-password/status', ConfirmedPasswordStatusController::class)
                ->name('filament-passkeys.confirm-password.status');
        });

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
        if (! $this->registerProfilePageSection) {
            return;
        }

        if (! $panel->hasProfile()) {
            return;
        }

        $profilePage = $panel->getProfilePage();

        if ($profilePage === null) {
            return;
        }

        $renderSection = fn (): string => view('filament-passkeys::passkey-section', [
            'passkeys' => static::resolveCurrentUserPasskeys(),
        ])->render();

        FilamentView::registerRenderHook(PanelsRenderHook::SIMPLE_PAGE_END, $renderSection, scopes: [$profilePage]);
        FilamentView::registerRenderHook(PanelsRenderHook::CONTENT_END, $renderSection, scopes: [$profilePage]);
    }
}
