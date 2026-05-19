<?php

declare(strict_types=1);

namespace RobertBoes\FilamentPasskeys\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ConfirmPasswordController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $user || ! Hash::check($request->input('password'), $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'password' => __('filament-passkeys::passkeys.password_confirm.invalid'),
            ]);
        }

        $request->session()->passwordConfirmed();

        return response()->noContent();
    }
}
