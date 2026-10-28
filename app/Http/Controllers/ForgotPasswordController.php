<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Tampilkan form untuk memasukkan email.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Proses pengiriman email reset password.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut.',
        ]);

        $email = strtolower($request->email);
        $user = User::where('email', $email)->first();

        // Batasi per email maksimal 3x reset dalam bulan ini
        $cacheKey = 'password_reset_monthly_attempts_'.md5($email).'_'.now()->format('Y_m');
        $attempts = (int) Cache::get($cacheKey, 0);

        if ($attempts >= 3) {
            return back()->withErrors(['email' => __('Terlalu banyak percobaan.')])->withInput();
        }

        // Generate token satu kali pakai (one-time token)
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Catat percobaan bulan ini
        Cache::put($cacheKey, $attempts + 1, now()->endOfMonth());

        // Buat signed route yang berlaku selama 60 menit dengan parameter token
        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(60),
            [
                'user' => $user->id,
                'token' => $token,
            ]
        );

        // Kirim email
        Mail::to($user->email)->send(new ResetPassword($user, $resetUrl));

        return back()->with('status', 'Tautan untuk mereset kata sandi telah dikirim ke email Anda.');
    }

    /**
     * Tampilkan form untuk membuat password baru.
     */
    public function showResetForm(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('password.request')->withErrors([
                'email' => __('Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.'),
            ]);
        }

        $token = $request->query('token');
        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        if (! $record || ! $token || ! Hash::check($token, $record->token)) {
            return redirect()->route('password.request')->withErrors([
                'email' => __('Tautan reset kata sandi tidak valid atau sudah pernah digunakan.'),
            ]);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            return redirect()->route('password.request')->withErrors([
                'email' => __('Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.'),
            ]);
        }

        return view('auth.reset-password', compact('user', 'token'));
    }

    /**
     * Proses penyimpanan password baru.
     */
    public function reset(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('password.request')->withErrors([
                'email' => __('Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.'),
            ]);
        }

        $token = $request->input('token', $request->query('token'));
        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        if (! $record || ! $token || ! Hash::check($token, $record->token)) {
            return redirect()->route('password.request')->withErrors([
                'email' => __('Tautan reset kata sandi tidak valid atau sudah pernah digunakan.'),
            ]);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            return redirect()->route('password.request')->withErrors([
                'email' => __('Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.'),
            ]);
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min' => __('Kata sandi minimal harus 8 karakter.'),
            'password.confirmed' => __('Konfirmasi kata sandi tidak cocok.'),
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        // Hapus token segera setelah digunakan agar tautan 1x pakai tidak dapat digunakan lagi
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return redirect()->route('login')->with('success', __('Kata sandi berhasil direset! Silakan masuk.'));
    }
}
