<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login()
    {
        // Get work principles for carousel (without img_url, using static image)
        $workPrinciples = DB::table('work_principles')
            ->where('category', 'prinsip')
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Admin/Auth/Login', [
            'workPrinciples' => $workPrinciples
        ]);
    }

    public function auth(Request $request)
    {
        // Validasi input wajib diisi (dengan pesan bahasa Indonesia)
        $validated = $request->validate(
            [
                'user' => ['required'],
                'password' => ['required'],
            ],
            [
                'user.required' => 'Kolom Username / Email wajib diisi.',
                'password.required' => 'Kolom Kata Sandi wajib diisi.',
            ]
        );

        $userField = filter_var($validated['user'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $userField => $validated['user'],
            'password' => $validated['password'],
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Check if user status is active
            if ($user->status === 'inactive') {
                Auth::logout();
                return redirect()->back()->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
            }

            if ($user->status === 'pending') {
                Auth::logout();
                return redirect()->back()->with('error', 'Akun Anda masih menunggu verifikasi. Silakan hubungi administrator.');
            }

            $request->session()->regenerate();
            return redirect()->intended('/dashboard')->with('success', 'Login berhasil');
        }

        return redirect()->back()->withErrors([
            'user' => 'Username atau kata sandi tidak sesuai.',
        ])->withInput($request->only('user', 'remember'));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ✅ Login API
    public function apiLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
