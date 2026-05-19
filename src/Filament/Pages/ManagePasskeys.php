<?php

namespace RobertBoes\FilamentPasskeys\Filament\Pages;

use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;

class ManagePasskeys extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $title = 'Passkeys';

    protected static ?string $slug = 'passkeys';

    protected string $view = 'filament-passkeys::filament.pages.manage-passkeys';

    public function getLayout(): string
    {
        return 'filament-panels::components.layout.simple';
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        unset($parameters['tenant']);

        $panelInstance = blank($panel)
            ? Filament::getCurrentOrDefaultPanel()
            : Filament::getPanel($panel);

        return route(static::getRouteName($panelInstance), $parameters, $isAbsolute);
    }

    protected function getViewData(): array
    {
        $user = filament()->auth()->user();

        return [
            'passkeys' => method_exists($user, 'passkeys')
                ? $user->passkeys()->latest()->get()
                : collect(),
        ];
    }
}
