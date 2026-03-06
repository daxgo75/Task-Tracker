<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponder;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(name="Authentication", description="Login dan logout pengguna")
 */
class AuthController extends Controller
{
    use ApiResponder;

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Authentication"},
     *     summary="Login pengguna",
     *     description="Autentikasi pengguna dengan email dan password. Mengembalikan Personal Access Token dengan waktu ekspirasi.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@tasktracker.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login berhasil."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="1|abc123..."),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2025-03-07 12:34:56"),
     *                 @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Kredensial tidak valid"),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Check if the account exists before attempting authentication
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->error(
                'Akun dengan email tersebut tidak ditemukan.',
                401,
                ['email' => ['Akun dengan email ini tidak ditemukan. Pastikan email yang Anda masukkan benar.']]
            );
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            return $this->error(
                'Password yang Anda masukkan salah.',
                401,
                ['password' => ['Password yang Anda masukkan salah. Silakan coba lagi.']]
            );
        }

        /** @var User $user */
        $user  = Auth::user();
        $token = $user->createToken('api-token');

        // Set token expiration time based on config
        $expirationMinutes = (int) config('sanctum.expiration');
        if ($expirationMinutes > 0) {
            $token->accessToken->update([
                'expires_at' => now()->addMinutes($expirationMinutes),
            ]);
        }

        return $this->success([
            'token'        => $token->plainTextToken,
            'expires_at'   => $token->accessToken->expires_at?->toDateTimeString(),
            'user'         => new UserResource($user),
        ], 'Login berhasil.');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Authentication"},
     *     summary="Logout pengguna",
     *     description="Mencabut (revoke) token aktif yang sedang digunakan.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout berhasil. Token telah dicabut.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout berhasil. Token telah dicabut.');
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     tags={"Authentication"},
     *     summary="Profil pengguna yang sedang login",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data pengguna",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()), 'Data pengguna berhasil diambil.');
    }
}
