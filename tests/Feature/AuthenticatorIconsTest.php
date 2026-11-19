<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Passkeys\Contracts\PasskeyRegistrationResponse;
use Laravel\Passkeys\Passkey;
use RobertBoes\FilamentPasskeys\Http\Responses\FilamentPasskeyRegistrationResponse;
use RobertBoes\FilamentPasskeys\Support\AuthenticatorIcons;

const APPLE_PASSWORDS_AAGUID = 'fbfc3007-154e-4ecc-8c0b-6e020557d7bd';
const CHROMIUM_BROWSER_AAGUID = 'b5397666-4885-aa6b-cebf-e52262a439a2';

function makePasskey(string $aaguid): Passkey
{
    return (new Passkey())->forceFill([
        'id' => 1,
        'name' => 'MacBook',
        'credential' => ['aaguid' => $aaguid],
    ]);
}

function jsonRequest(): Request
{
    return Request::create('/user/passkeys', 'POST', server: ['HTTP_ACCEPT' => 'application/json']);
}

it('resolves both icon variants for a known authenticator', function () {
    $icons = AuthenticatorIcons::for(makePasskey(APPLE_PASSWORDS_AAGUID));

    expect($icons['authenticator'])->toBe('Apple Passwords')
        ->and($icons['iconLight'])->toStartWith('data:image/svg+xml;base64,')
        ->and($icons['iconDark'])->toStartWith('data:image/svg+xml;base64,');
});

it('resolves no icons for an authenticator without one', function () {
    expect(AuthenticatorIcons::for(makePasskey(CHROMIUM_BROWSER_AAGUID)))->toBe([
        'authenticator' => 'Chromium Browser',
        'iconLight' => null,
        'iconDark' => null,
    ]);
});

it('passes the authenticator icons to the passkey list', function () {
    $rendered = view('filament-passkeys::passkey-manager', [
        'passkeys' => collect([makePasskey(APPLE_PASSWORDS_AAGUID)]),
    ])->render();

    expect($rendered)
        ->toContain('Apple Passwords')
        ->toContain('data:image')
        ->toContain('fi-filament-passkeys-authenticator-icon-dark');
});

it('binds the registration response that adds the authenticator icons', function () {
    expect(app(PasskeyRegistrationResponse::class))
        ->toBeInstanceOf(FilamentPasskeyRegistrationResponse::class);
});

it('adds the authenticator name and icons to the registration JSON', function () {
    $response = app(PasskeyRegistrationResponse::class)
        ->withPasskey(makePasskey(APPLE_PASSWORDS_AAGUID))
        ->toResponse(jsonRequest());

    expect($response)->toBeInstanceOf(JsonResponse::class);

    $data = $response->getData(true);

    expect($data)
        ->toMatchArray([
            'status' => 'passkey-registered',
            'id' => '1',
            'name' => 'MacBook',
            'authenticator' => 'Apple Passwords',
        ])
        ->and($data['iconLight'])->toStartWith('data:image/svg+xml;base64,')
        ->and($data['iconDark'])->toStartWith('data:image/svg+xml;base64,');
});

it('keeps redirecting back for non-JSON registration requests', function () {
    $response = app(PasskeyRegistrationResponse::class)
        ->withPasskey(makePasskey(APPLE_PASSWORDS_AAGUID))
        ->toResponse(Request::create('/user/passkeys', 'POST'));

    expect($response)->toBeInstanceOf(RedirectResponse::class);
});
