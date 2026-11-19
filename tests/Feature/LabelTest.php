<?php

declare(strict_types=1);

use RobertBoes\FilamentPasskeys\FilamentPasskeysPlugin;

it('falls back to the translations when the config labels are null', function () {
    config([
        'filament-passkeys.login_button_label' => null,
        'filament-passkeys.user_menu_item_label' => null,
    ]);

    $plugin = FilamentPasskeysPlugin::make();

    expect($plugin->getLoginButtonLabel())->toBe('Sign in with passkey')
        ->and($plugin->getUserMenuItemLabel())->toBe(__('filament-passkeys::passkeys.menu.label'));
});

it('uses the config labels when they are set', function () {
    config([
        'filament-passkeys.login_button_label' => 'Use a passkey',
        'filament-passkeys.user_menu_item_label' => 'My keys',
    ]);

    $plugin = FilamentPasskeysPlugin::make();

    expect($plugin->getLoginButtonLabel())->toBe('Use a passkey')
        ->and($plugin->getUserMenuItemLabel())->toBe('My keys');
});

it('keeps an empty config label instead of falling back', function () {
    config(['filament-passkeys.login_button_label' => '']);

    expect(FilamentPasskeysPlugin::make()->getLoginButtonLabel())->toBe('');
});

it('prefers the fluent labels over the config labels', function () {
    config(['filament-passkeys.login_button_label' => 'From config']);

    $plugin = FilamentPasskeysPlugin::make()
        ->loginButtonLabel('From plugin')
        ->userMenuItemLabel('');

    expect($plugin->getLoginButtonLabel())->toBe('From plugin')
        ->and($plugin->getUserMenuItemLabel())->toBe('');
});

it('rejects a config label that is neither a string nor null', function () {
    config(['filament-passkeys.login_button_label' => 123]);

    FilamentPasskeysPlugin::make()->getLoginButtonLabel();
})->throws(InvalidArgumentException::class);
