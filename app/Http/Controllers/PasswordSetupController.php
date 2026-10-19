<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordSetupController extends Controller
{
    public function show(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Tautan tidak valid atau sudah kedaluwarsa.');
        }

        if ($user->email_verified_at !== null) {
            return redirect('/')->with('auth_error', 'Tautan tidak valid. Akun Anda sudah melakukan setup password sebelumnya.');
        }

        $step = session('pin_verified_'.$user->id) ? 2 : 1;

        return view('auth.setup-password', compact('user', 'step'));
    }

    public function store(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Tautan tidak valid atau sudah kedaluwarsa.');
        }

        if ($user->email_verified_at !== null) {
            return redirect('/')->with('auth_error', 'Tautan tidak valid. Akun Anda sudah melakukan setup password sebelumnya.');
        }

        // Jika form mengirimkan PIN (Step 1)
        if ($request->has('pin')) {
            $request->validate([
                'pin' => 'required|string|size:4',
            ]);

            if ($request->pin !== $user->pin) {
                return back()->withErrors(['pin' => 'PIN yang Anda masukkan salah.']);
            }

            session(['pin_verified_'.$user->id => true]);

            return back();
        }

        // Jika form mengirimkan password (Step 2)
        if (! session('pin_verified_'.$user->id)) {
            return back()->withErrors(['pin' => 'Sesi kedaluwarsa. Silakan masukkan PIN ulang.']);
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($request->password);
        $user->email_verified_at = now();
        $user->save();

        session()->forget('pin_verified_'.$user->id);

        return redirect()->route('setup-password.success');
    }

    public function success()
    {
        return view('auth.setup-success');
    }
}
