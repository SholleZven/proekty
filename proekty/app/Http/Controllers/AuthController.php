<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['message' => 'Неверный логин или пароль'], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => auth('api')->user(),
        ]);
    }

    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Успешно вышли из системы']);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function refresh(): JsonResponse
    {
        return response()->json([
            'token' => JWTAuth::refresh(),
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }
}
