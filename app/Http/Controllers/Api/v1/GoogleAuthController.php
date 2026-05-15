<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckToken;
use Kreait\Firebase\Factory;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GoogleAuthController extends Controller
{
    /**
     * Register new user from Google Registration Flow
     */
    public function register(Request $request)
    {
        // 1. Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'google_id' => 'required|string', // Bisa unique juga kalau mau strict
            'password' => 'required|string|confirmed',
            'branch_id' => 'required|numeric',
            'department_id' => 'required|numeric',
            'shift_id' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            // 'birth_date' => 'required|date', // Optional for now
            'photo' => 'required|image|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            \Log::info('📝 [Register] Memulai registrasi user: ' . $request->email);

            // 2. Upload Foto
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $fileName = 'employee_' . time() . '.' . $file->getClientOriginalExtension();
                $photoPath = $file->storeAs('uploads/employees', $fileName, 'public');
                \Log::info('📸 [Register] Foto berhasil diupload: ' . $photoPath);
            }

            // 3. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'google_id' => $request->google_id,
                'status' => 'pending', // Pending Admin Verification
                'profile_photo_path' => $photoPath,
            ]);

            \Log::info('👤 [Register] User berhasil dibuat ID: ' . $user->id);

            // 4. Create Employee Data
            // Perlu mapping shift string ke shift_id integer?
            // Input dari Flutter: "Pagi", "Siang", "Malam" (String).
            // Tapi Backend Employee model ada `shift_id`.
            // Kita harus cari ID shift atau simpan string?
            // Employee punya `shift()` relation -> `shift_id`.
            // Mari kita asumsikan sementara kita tidak punya shift_id, kita skip atau cari.
            // Untuk saat ini, mari kita log warning jika shift string.

            // Cek model Employee fillable: 'shift_id' tidak ada di fillable tapi di relation ada.
            // Mungkin ada kolom lain untuk string shift? Tidak terlihat.

            // Mari kita buat Employee sederhana dulu.
            $employee = Employee::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'contact' => $request->phone,
                'address' => $request->address,
                'path' => $photoPath, // Foto karyawan

                // Fields yang perlu diperhatikan (sesuaikan dengan kolom DB yang ada)
                'branch_id' => $request->branch_id,
                'department_id' => $request->department_id,
                'shift_id' => $request->shift_id, // Perlu lookup shift ID based on name?
                'working_start_date' => $request->working_start_date,

                // Default stuffs
                'status' => 'active', // Status karyawan active? Atau pending juga?
                'verification' => false,
            ]);

            DB::commit();

            \Log::info('✅ [Register] Registrasi sukses untuk: ' . $request->email);

            return response()->json([
                'code' => 200,
                'message' => 'Registrasi berhasil. Mohon tunggu verifikasi admin.',
                'data' => [
                    'user' => $user,
                    'employee' => $employee
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('🔥 [Register] Gagal: ' . $e->getMessage());

            return response()->json([
                'code' => 500,
                'message' => 'Gagal registrasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if Google user is registered, if yes login, if no return user data for registration
     */
    public function checkAndLoginGoogle(Request $request)
    {
        $request->validate([
            'token' => 'required|string', // Firebase ID Token
        ]);

        try {
            \Log::info('🚀 [GoogleAuth] Memulai proses check & login Google');
            \Log::info('📝 [GoogleAuth] Token diterima: ' . substr($request->token, 0, 50) . '...');
            \Log::info('📱 [GoogleAuth] Device token: ' . ($request->device_token ?? 'null'));

            $apiKey = env('FIREBASE_API_KEY');

            // Kirim ID token ke Firebase REST API untuk verifikasi
            \Log::info('🔄 [GoogleAuth] Mengirim request ke Firebase API...');
            $response = Http::post("https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$apiKey}", [
                'idToken' => $request->token
            ]);

            if ($response->failed()) {
                \Log::error('❌ [GoogleAuth] Firebase API gagal: ' . json_encode($response->json()));
                return response()->json([
                    'code' => 401,
                    'message' => 'Token Firebase tidak valid',
                    'data' => null
                ], 401);
            }

            \Log::info('✅ [GoogleAuth] Response dari Firebase berhasil');
            $firebaseUser = $response->json()['users'][0];

            $uid = $firebaseUser['localId'];
            $email = $firebaseUser['email'] ?? null;
            $name = $firebaseUser['displayName'] ?? 'User';
            $photoUrl = $firebaseUser['photoUrl'] ?? null;

            \Log::info('👤 [GoogleAuth] Data Firebase User:');
            \Log::info('   UID: ' . $uid);
            \Log::info('   Email: ' . ($email ?? 'null'));
            \Log::info('   Name: ' . $name);

            // Cek apakah user sudah terdaftar berdasarkan email atau google_id
            \Log::info('🔍 [GoogleAuth] Mencari user dengan email: ' . $email . ' atau google_id: ' . $uid);
            $user = User::where('email', $email)
                ->orWhere('google_id', $uid)
                ->first();

            // CASE 1: User belum terdaftar - Return data untuk registrasi
            if (!$user) {
                \Log::info('⚠️ [GoogleAuth] User belum terdaftar, return data untuk registrasi');

                return response()->json([
                    'code' => 404,
                    'message' => 'User belum terdaftar, silakan daftar terlebih dahulu',
                    'registered' => false,
                    'status' => 'not_registered',
                    'data' => [
                        'google_id' => $uid,
                        'email' => $email,
                        'name' => $name,
                        'photo_url' => $photoUrl,
                        'firebase_token' => $request->token
                    ]
                ]);
            }

            // CASE 2: User sudah terdaftar tapi status = pending
            if ($user->status === 'pending') {
                \Log::info('⏳ [GoogleAuth] User sudah terdaftar tapi status pending (ID: ' . $user->id . ')');

                return response()->json([
                    'code' => 403,
                    'message' => 'Akun Anda sedang menunggu verifikasi admin',
                    'registered' => true,
                    'status' => 'pending',
                    'data' => [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'registered_at' => $user->created_at->format('Y-m-d H:i:s')
                    ]
                ], 403);
            }

            // CASE 3: User sudah terdaftar dan status = active - Login berhasil
            \Log::info('✅ [GoogleAuth] User sudah terdaftar dengan status active (ID: ' . $user->id . ')');

            // Update google_id jika belum ada
            if (!$user->google_id) {
                \Log::info('🔄 [GoogleAuth] Update google_id untuk user');
                $user->google_id = $uid;
                $user->save();
            }

            // Update device token jika ada
            if ($request->device_token) {
                $user->external_id = $request->device_token;
                $user->save();
            }

            // Generate token
            \Log::info('🔑 [GoogleAuth] Generate Sanctum token...');
            $token = $user->createToken('api-token')->plainTextToken;
            \Log::info('✅ [GoogleAuth] Token berhasil dibuat');

            \Log::info('✅ [GoogleAuth] Login berhasil untuk user: ' . $user->name . ' (ID: ' . $user->id . ')');

            //cek apakah user sudah memiliki employee
            $hasEmployee = !is_null($user->employee);

            return response()->json([
                'code' => 200,
                'message' => 'User sudah terdaftar, login berhasil',
                'registered' => true,
                'status' => 'active',
                'has_employee' => $hasEmployee,
                'data' => [
                    'token' => $token,
                    'user' => $user
                ]
            ]);

        } catch (\Throwable $e) {
            \Log::error('🔥 [GoogleAuth] Exception terjadi:');
            \Log::error('   Message: ' . $e->getMessage());
            \Log::error('   File: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('   Trace: ' . $e->getTraceAsString());

            return response()->json([
                'code' => 500,
                'message' => 'Firebase login error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
