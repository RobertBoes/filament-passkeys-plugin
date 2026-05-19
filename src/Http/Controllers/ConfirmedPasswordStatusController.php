<?php

namespace RobertBoes\FilamentPasskeys\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Date;

class ConfirmedPasswordStatusController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $lastConfirmation = (int) $request->session()->get('auth.password_confirmed_at', 0);

        $elapsed = Date::now()->unix() - $lastConfirmation;

        $timeout = (int) $request->input(
            'seconds',
            config('auth.password_timeout', 10800),
        );

        return response()->json([
            'confirmed' => $elapsed < $timeout,
        ]);
    }
}
