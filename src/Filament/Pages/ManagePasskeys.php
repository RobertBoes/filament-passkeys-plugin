<?php

namespace RobertBoes\FilamentPasskeys\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class ManagePasskeys extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $title = 'Passkeys';

    protected static ?string $slug = 'passkeys';

    protected string $view = 'filament-passkeys::filament.pages.manage-passkeys';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
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
