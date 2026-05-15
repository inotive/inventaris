<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        Log::info('Login request', $request->all());
        $login = $request->input('login') ?? $request->input('username') ?? $request->input('email');
        $password = $request->input('password');

        if (!$login || !$password) {
            return ResponseFormatter::error('Field login dan password wajib diisi', 422);
        }

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::attempt([$fieldType => $login, 'password' => $password])) {
            return ResponseFormatter::error('Username atau password anda salah', 401);
        }

        $user = Auth::user();

        // Check if user status is active
        if ($user->status === 'inactive') {
            Auth::logout();
            return ResponseFormatter::error('Akun Anda tidak aktif. Silakan hubungi administrator.', 403);
        }

        if ($user->status === 'pending') {
            Auth::logout();
            return ResponseFormatter::error('Akun Anda masih menunggu verifikasi. Silakan hubungi administrator.', 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $user->external_id = $request->input('device_token');
        $user->last_seen = now();
        $user->save();

        return ResponseFormatter::success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Anda berhasil login');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success(null, 'Anda berhasil logout');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->old_password, $user->password)) {
            return ResponseFormatter::error('Kata sandi lama tidak sesuai', 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return ResponseFormatter::success(null, 'Password berhasil diubah.');
    }
}
