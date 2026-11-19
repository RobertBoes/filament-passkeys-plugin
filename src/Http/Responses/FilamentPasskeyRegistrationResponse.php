<?php

declare(strict_types=1);

namespace RobertBoes\FilamentPasskeys\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Passkeys\Http\Responses\PasskeyRegistrationResponse;
use Laravel\Passkeys\Passkey;
use RobertBoes\FilamentPasskeys\Support\AuthenticatorIcons;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds the authenticator name and icons to the JSON response, so a passkey
 * added on the profile page shows its icon without a page reload.
 */
class FilamentPasskeyRegistrationResponse extends PasskeyRegistrationResponse
{
    public function toResponse($request): Response
    {
        $response = parent::toResponse($request);

        if (! $response instanceof JsonResponse || ! $this->passkey instanceof Passkey) {
            return $response;
        }

        /** @var array<string, mixed> $data */
        $data = $response->getData(true);

        return $response->setData([
            ...$data,
            ...AuthenticatorIcons::for($this->passkey),
        ]);
    }
}
