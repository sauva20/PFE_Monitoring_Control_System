<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

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

        $user = User::where('email', $request->email)->first();

        // Buat signed route yang berlaku selama 60 menit
        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(60),
            ['user' => $user->id]
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
            abort(401, 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.');
        }

        return view('auth.reset-password', compact('user'));
    }

    /**
     * Proses penyimpanan password baru.
     */
    public function reset(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')->with('success', __('Kata sandi berhasil direset! Silakan masuk.'));
    }
}
