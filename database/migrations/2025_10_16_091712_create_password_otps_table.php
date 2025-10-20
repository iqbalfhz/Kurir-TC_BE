<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordOtpController extends Controller
{
    // POST /api/auth/send-otp
    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
        ]);

        // Pastikan user terdaftar (kalau mau "silent", return 200 juga kalau tidak ada)
        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            // anti-enumeration: balas 200 walau email tidak ada
            return response()->json(['message' => 'Jika email terdaftar, OTP telah dikirim.']);
        }

        // buat OTP 6 digit & simpan di cache (10 menit)
        $otp = (string) random_int(100000, 999999);
        $key = 'pwd_otp:'.strtolower($data['email']);
        Cache::put($key, $otp, now()->addMinutes(10));

        // kirim email sederhana
        Mail::raw("Kode OTP Anda: {$otp}\nBerlaku 10 menit.", function ($m) use ($data) {
            $m->to($data['email'])->subject('OTP Reset Password');
        });

        return response()->json(['message' => 'OTP terkirim. Periksa email Anda.']);
    }

    // POST /api/auth/reset-password
    public function reset(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'otp' => ['required','digits_between:4,6'],
            'password' => ['required','confirmed','min:6'],
        ]);

        $key = 'pwd_otp:'.strtolower($data['email']);
        $cached = Cache::get($key);

        if (! $cached || $cached !== $data['otp']) {
            throw ValidationException::withMessages(['otp' => ['OTP tidak valid atau sudah kedaluwarsa.']]);
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->forceFill([
            'password' => bcrypt($data['password']),
        ])->save();

        // hapus OTP setelah sukses
        Cache::forget($key);

        return response()->json(['message' => 'Password berhasil direset.']);
    }
}
