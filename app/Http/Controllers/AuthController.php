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

        // Buat key unik berdasarkan email dan IP address
        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        // Cek apakah sudah melebihi 5 kali percobaan
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors([
                'email' => 'Akun Anda diblokir sementara karena terlalu banyak percobaan masuk.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            // Hapus blokir jika berhasil login
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();
            $request->session()->forget('is_pin_login');

            return redirect()->intended('/dashboard')->with('success', __('Login berhasil! Selamat datang.'));
        }

        // Catat percobaan gagal (blokir selama 10 menit = 600 detik)
        RateLimiter::hit($throttleKey, 600);

        return back()->withErrors([
            'email' => __('Email atau Password yang Anda masukkan salah.'),
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
