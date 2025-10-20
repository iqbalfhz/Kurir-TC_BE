<?php

namespace App\Http\Controllers\Api;

use Log;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PasswordOtpController extends Controller
{
    // POST /api/auth/send-otp
    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            // Hindari user enumeration
            return response()->json(['message' => 'Jika email terdaftar, OTP telah dikirim.']);
        }

        $otp = (string) random_int(100000, 999999);
        $key = 'pwd_otp:'.strtolower($data['email']);
        Cache::put($key, $otp, now()->addMinutes(10));

        try {
            Mail::raw("Kode OTP Anda: {$otp}\nBerlaku 10 menit.", function ($m) use ($data) {
                $m->to($data['email'])->subject('OTP Reset Password');
            });
        } catch (\Throwable $e) {
            // Catat tapi jangan bikin 500
            Log::error('send-otp mail error: '.$e->getMessage());
        }

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
            throw ValidationException::withMessages([
                'otp' => ['OTP tidak valid atau sudah kedaluwarsa.'],
            ]);
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->forceFill(['password' => bcrypt($data['password'])])->save();

        Cache::forget($key);

        return response()->json(['message' => 'Password berhasil direset.']);
    }
}
