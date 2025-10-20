<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\LoginResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): LoginResource
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            // 422 agar mudah ditangani di klien
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        /** @var User $user */
        $user = User::where('email', $credentials['email'])->firstOrFail();

        // Opsional: single-session (hapus token lama)
        $user->tokens()->delete();

        // Buat token baru (tanpa abilities untuk sekarang)
        $token = $user->createToken('mobile', [])->plainTextToken;

        return new LoginResource([
            'token' => $token,
            'user'  => $user,
        ]);
        // LoginResource akan membungkus respons { data: { token, user } }
    }

    /**
     * GET /api/auth/me  (auth:sanctum)
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * POST /api/auth/logout  (auth:sanctum)
     * Menghapus token yang sedang dipakai.
     */
    public function logout(Request $request): Response
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->noContent(); // ini mengembalikan Illuminate\Http\Response
    }
}
