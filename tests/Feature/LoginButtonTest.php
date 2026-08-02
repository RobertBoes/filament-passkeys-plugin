<?php

use Filament\Auth\Pages\Login;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use RobertBoes\FilamentPasskeys\FilamentPasskeysPlugin;

afterEach(function () {
    HandleComponents::$componentStack = [];
});

it('renders the login button on the credentials step', function () {
    HandleComponents::$componentStack = [new Login()];

    expect(FilamentPasskeysPlugin::make()->renderLoginButton())
        ->toContain('filamentPasskeysLogin');
});

it('renders the login button when no Livewire component is rendering', function () {
    expect(FilamentPasskeysPlugin::make()->renderLoginButton())
        ->toContain('filamentPasskeysLogin');
});

it('hides the login button during the multi-factor challenge', function () {
    $login = new Login();
    $login->userUndertakingMultiFactorAuthentication = encrypt(1);

    HandleComponents::$componentStack = [$login];

    expect(FilamentPasskeysPlugin::make()->renderLoginButton())->toBe('');
});
