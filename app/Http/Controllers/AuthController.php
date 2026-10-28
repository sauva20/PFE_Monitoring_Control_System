<?php

namespace App\Http\Controllers;

use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Pengecekan Honeypot
        // Jika field '_contact_number' terisi, maka itu pasti Bot (karena disembunyikan dari manusia)
        if (! empty($request->input('_contact_number'))) {
            return back()->withErrors([
                'email' => __('Email atau Password yang Anda masukkan salah.'),
            ])->onlyInput('email');
        }

        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];

        $messages = [];

        if (config('services.recaptcha.site_key')) {
            $rules['g-recaptcha-response'] = ['required', new Recaptcha];
            $messages['g-recaptcha-response.required'] = __('Harap centang verifikasi reCAPTCHA.');
        }

        $request->validate($rules, $messages);

        $credentials = $request->only('email', 'password');

        // 1. Throttle Akun spesifik (email + IP) - batas 5x salah
        $accountThrottleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        // 2. Throttle Global IP (mencegah gonta-ganti email / password spraying) - batas 10x salah
        $ipThrottleKey = 'login-ip:'.$request->ip();

        // Cek apakah IP diblokir secara global (10x percobaan gagal)
        if (RateLimiter::tooManyAttempts($ipThrottleKey, 10)) {
            return back()->withErrors([
                'email' => __('Akses masuk diblokir sementara karena terlalu banyak percobaan gagal dari perangkat ini.'),
            ])->onlyInput('email');
        }

        // Cek apakah akun ini diblokir (5x percobaan gagal)
        if (RateLimiter::tooManyAttempts($accountThrottleKey, 5)) {
            return back()->withErrors([
                'email' => __('Akun Anda diblokir sementara karena terlalu banyak percobaan masuk.'),
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            // Hapus blokir jika berhasil login
            RateLimiter::clear($accountThrottleKey);
            RateLimiter::clear($ipThrottleKey);

            $request->session()->regenerate();
            $request->session()->forget('is_pin_login');

            return redirect()->intended('/dashboard')->with('success', __('Login berhasil! Selamat datang.'));
        }

        // Catat percobaan gagal (blokir selama 10 menit = 600 detik)
        RateLimiter::hit($accountThrottleKey, 600);
        RateLimiter::hit($ipThrottleKey, 600);

        if (RateLimiter::tooManyAttempts($ipThrottleKey, 10)) {
            return back()->withErrors([
                'email' => __('Akses masuk diblokir sementara karena terlalu banyak percobaan gagal dari perangkat ini.'),
            ])->onlyInput('email');
        }

        if (RateLimiter::tooManyAttempts($accountThrottleKey, 5)) {
            return back()->withErrors([
                'email' => __('Akun Anda diblokir sementara karena terlalu banyak percobaan masuk.'),
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => __('Email atau Password yang Anda masukkan salah.'),
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 1990 00:00:00 GMT',
        ]);
    }
}
