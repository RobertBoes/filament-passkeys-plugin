<?php

declare(strict_types=1);

namespace RobertBoes\FilamentPasskeys\Http\Responses;

use Filament\Facades\Filament;
use Illuminate\Http\JsonResponse;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse as PasskeyLoginResponseContract;

class FilamentPasskeyLoginResponse implements PasskeyLoginResponseContract
{
    public function toResponse($request): JsonResponse
    {
        $panel = Filament::getCurrentOrDefaultPanel();

        return new JsonResponse([
            'redirect' => $panel?->getUrl() ?? config('app.url'),
        ]);
    }
}
