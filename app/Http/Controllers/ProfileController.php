<?php

namespace App\Http\Controllers;

use App\Models\GuestDisplay;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, auth()->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini tidak sesuai.',
            ]);
        }

        auth()->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', __('Kata sandi berhasil diubah!'));
    }

    public function updatePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required|digits:4',
            'new_pin' => 'required|digits:4|confirmed',
        ]);

        $currentPinValid = auth()->user()->matchesPin($request->current_pin);

        if (! $currentPinValid) {
            throw ValidationException::withMessages([
                'current_pin' => __('PIN saat ini tidak sesuai.'),
            ]);
        }

        $newPin = $request->new_pin;
        $isPinUsedInGuest = GuestDisplay::where('pin', $newPin)->exists();
        $isPinUsedInUsers = User::whereNotNull('pin')
            ->where('id', '!=', auth()->id())
            ->get()
            ->contains(function ($u) use ($newPin) {
                return $u->matchesPin($newPin);
            });

        if ($isPinUsedInGuest || $isPinUsedInUsers) {
            throw ValidationException::withMessages([
                'new_pin' => __('PIN yang anda buat berbahaya dan tidak aman'),
            ]);
        }

        auth()->user()->update([
            'pin' => $request->new_pin,
        ]);

        return back()->with('success', __('PIN berhasil diubah!'));
    }
}
