<?php

declare(strict_types=1);

namespace RobertBoes\FilamentPasskeys\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

class ConfirmedPasswordStatusController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $lastConfirmation = $request->session()->get('auth.password_confirmed_at', 0);
        $lastConfirmation = is_numeric($lastConfirmation) ? (int) $lastConfirmation : 0;

        $elapsed = Date::now()->unix() - $lastConfirmation;

        $timeout = $request->integer(
            'seconds',
            Config::integer('auth.password_timeout', 10800),
        );

        return response()->json([
            'confirmed' => $elapsed < $timeout,
        ]);
    }
}
