<?php

declare(strict_types=1);

it('renders the passkey-manager view when no panel is current but a default exists', function () {
    // FilamentManager::getCurrentPanel() is only populated by Filament's HTTP
    // middleware on real requests. In Livewire::test() or any other context
    // without middleware it stays null, so the view must fall back to the
    // default panel via getCurrentOrDefaultPanel().
    //
    // The test panel is registered by TestPanelProvider in TestCase.

    expect(filament()->getCurrentPanel())->toBeNull();

    $rendered = view('filament-passkeys::passkey-manager', ['passkeys' => collect()])->render();

    expect($rendered)->toContain('fi-filament-passkeys-manager');
});
