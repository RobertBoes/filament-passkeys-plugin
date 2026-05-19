<?php

declare(strict_types=1);

use Filament\Schemas\Components\Section;
use RobertBoes\FilamentPasskeys\FilamentPasskeysPlugin;

it('resolves the plugin as a singleton from the container', function () {
    $first = FilamentPasskeysPlugin::make();
    $second = FilamentPasskeysPlugin::make();

    expect($first)->toBeInstanceOf(FilamentPasskeysPlugin::class)
        ->and($first)->toBe($second);
});

it('exposes a stable plugin id', function () {
    expect(FilamentPasskeysPlugin::make()->getId())->toBe('filament-passkeys');
});

it('builds the passkeys section as a Filament schema Section', function () {
    $section = FilamentPasskeysPlugin::passkeysSection();

    expect($section)->toBeInstanceOf(Section::class)
        ->and($section->isCompact())->toBeTrue();
});

it('toggles surfaces via with*/without* methods', function () {
    $plugin = FilamentPasskeysPlugin::make()
        ->withManagePage()
        ->withoutLoginButton()
        ->withoutProfilePageSection();

    expect($plugin)->toBeInstanceOf(FilamentPasskeysPlugin::class);
});

it('registers the install command', function () {
    $this->artisan('filament-passkeys:install', ['--no-migrate' => true])
        ->expectsConfirmation('Publish the filament-passkeys config too?', 'no')
        ->assertSuccessful();
});
