<?php

namespace App\Http\Controllers;

use App\Models\Apar;
use App\Models\Gedung;
use App\Models\JenisApar;
use App\Models\KapasitasApar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class ScanController extends Controller
{
    public function index(Request $request, $kode)
    {
        $apar = Apar::with([
            'lokasi.gedung',
            'jenis',
            'kapasitas',
            'inspeksis' => function ($query) {
                $query->latest()->with('user');
            },
        ])->where('kode', $kode)->firstOrFail();

        // Jika ada sesi login (terutama sesi PIN inspeksi), hapus langsung saat kembali ke browser scan
        // sehingga petugas wajib memasukkan PIN 4 digit kembali jika ingin melakukan inspeksi lagi
        if (session('is_pin_login') || Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $latestInspeksi = $apar->inspeksis->first();

        $gedungs = Gedung::with('lokasi')->get();
        $jenisApars = JenisApar::all();
        $kapasitasApars = KapasitasApar::all();

        return response()
            ->view('scan.index', compact('apar', 'latestInspeksi', 'gedungs', 'jenisApars', 'kapasitasApars'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function verifyPin(Request $request, $kode)
    {
        $request->validate([
            'pin' => 'required|string',
        ]);

        $apar = Apar::where('kode', $kode)->firstOrFail();

        // Rate Limiting: Maksimal 5 percobaan dalam 10 menit (600 detik) per IP
        $key = 'verify-pin:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return redirect()->back()->with('error', __('Akses verifikasi PIN diblokir sementara karena terlalu banyak percobaan salah.'));
        }

        // Find user by PIN (Loop through all users because PIN might be hashed or plaintext)
        $users = User::whereNotNull('pin')->get();
        $user = $users->first(function ($u) use ($request) {
            return $u->matchesPin($request->pin);
        });

        if (! $user) {
            RateLimiter::hit($key, 600); // Blokir 10 menit jika 5x gagal

            if (RateLimiter::tooManyAttempts($key, 5)) {
                return redirect()->back()->with('error', __('Akses verifikasi PIN diblokir sementara karena terlalu banyak percobaan salah.'));
            }

            return redirect()->back()->with('error', __('PIN tidak valid atau tidak ditemukan.'));
        }

        RateLimiter::clear($key);

        // Log the user in so they can access the inspection form
        Auth::login($user);

        // Tandai bahwa ini adalah login via PIN (inspeksi)
        session([
            'is_pin_login' => true,
            'pin_login_apar_id' => $apar->id,
        ]);

        if ($request->input('action') === 'update') {
            return redirect()->route('scan.apar', ['kode' => $apar->kode, 'edit' => 1]);
        }

        // Redirect to start inspection
        return redirect()->route('inspeksi.pedoman', $apar->id);
    }
}
