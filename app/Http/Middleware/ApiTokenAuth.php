<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTokenAuth
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function handle(Request $request, Closure $next): JsonResponse|\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = $this->authService->validateToken($token);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Auth::setUser($user);

        return $next($request);
    }
}

