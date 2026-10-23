<?php

namespace App\Http\Controllers;

use App\Mail\GuestDisplayAccessMail;
use App\Models\GuestDisplay;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GuestDisplayController extends Controller
{
    private function ensureAdmin(): void
    {
        if (auth()->user()?->role !== 'EHSS') {
            abort(403, 'Akses terbatas untuk Administrator EHSS.');
        }
    }

    /**
     * Display a listing of guest display credentials (Admin EHSS).
     */
    public function index(Request $request): View
    {
        $this->ensureAdmin();

        $query = GuestDisplay::with('creator')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('institution', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true)
                    ->whereDate('valid_until', '>=', Carbon::today());
            } elseif ($request->input('status') === 'expired') {
                $query->where(function ($q) {
                    $q->where('is_active', false)
                        ->orWhereDate('valid_until', '<', Carbon::today());
                });
            }
        }

        $guests = $query->paginate(10)->withQueryString();

        return view('guest-display.index', compact('guests'));
    }

    /**
     * Store a newly created guest display credential and send email.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'pin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
            'valid_until' => 'required|date|after_or_equal:today',
        ], [
            'name.required' => 'Nama viewer wajib diisi.',
            'institution.required' => 'Instansi/Lembaga wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'pin.required' => 'PIN akses 4 digit wajib diisi.',
            'pin.size' => 'PIN harus tepat 4 digit angka.',
            'pin.regex' => 'PIN harus berupa 4 digit angka.',
            'valid_until.required' => 'Batas tanggal berlaku wajib diisi.',
            'valid_until.after_or_equal' => 'Batas tanggal berlaku tidak boleh sebelum hari ini.',
        ]);

        $token = Str::random(40);
        while (GuestDisplay::where('access_token', $token)->exists()) {
            $token = Str::random(40);
        }

        $guest = GuestDisplay::create([
            'name' => $validated['name'],
            'institution' => $validated['institution'],
            'email' => $validated['email'],
            'pin' => $validated['pin'],
            'access_token' => $token,
            'valid_until' => $validated['valid_until'],
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        // Send credentials email
        $mailSuccess = true;
        try {
            Mail::to($guest->email)->send(new GuestDisplayAccessMail($guest));
        } catch (\Throwable $e) {
            $mailSuccess = false;
            Log::error('Gagal mengirim email kredensial guest display: '.$e->getMessage(), [
                'guest_id' => $guest->id,
                'email' => $guest->email,
            ]);
        }

        if ($mailSuccess) {
            return redirect()->route('display-access.index')
                ->with('success', "Akses Viewer Display berhasil dibuat dan email berisi PIN telah dikirimkan ke {$guest->email}.");
        }

        return redirect()->route('display-access.index')
            ->with('success', "Akses Viewer Display berhasil dibuat, namun email gagal terkirim secara otomatis. Silakan gunakan tombol 'Kirim Ulang Email' atau bagikan tautan secara langsung.");
    }

    /**
     * Resend the credentials email to guest.
     */
    public function resendEmail(GuestDisplay $guestDisplay): RedirectResponse
    {
        $this->ensureAdmin();

        try {
            Mail::to($guestDisplay->email)->send(new GuestDisplayAccessMail($guestDisplay));

            return back()->with('success', "Email kredensial akses display berhasil dikirim ulang ke {$guestDisplay->email}.");
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim ulang email guest display: '.$e->getMessage());

            return back()->with('error', 'Gagal mengirim email: '.$e->getMessage());
        }
    }

    /**
     * Update an existing guest display credential.
     */
    public function update(Request $request, GuestDisplay $guestDisplay): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'pin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
            'valid_until' => 'required|date',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama viewer wajib diisi.',
            'institution.required' => 'Instansi/Lembaga wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'pin.required' => 'PIN akses 4 digit wajib diisi.',
            'pin.size' => 'PIN harus tepat 4 digit angka.',
            'pin.regex' => 'PIN harus berupa 4 digit angka.',
            'valid_until.required' => 'Batas tanggal berlaku wajib diisi.',
        ]);

        $isActive = $request->boolean('is_active');
        $validUntil = Carbon::parse($validated['valid_until']);

        if ($isActive && $validUntil->endOfDay()->isPast()) {
            return back()->withErrors([
                'valid_until' => __('Masa berlaku tidak boleh tanggal yang sudah lewat jika status akses diaktifkan. Silakan pilih tanggal hari ini atau masa mendatang.'),
            ])->withInput();
        }

        $guestDisplay->update([
            'name' => $validated['name'],
            'institution' => $validated['institution'],
            'email' => $validated['email'],
            'pin' => $validated['pin'],
            'valid_until' => $validated['valid_until'],
            'is_active' => $isActive,
        ]);

        return redirect()->route('display-access.index')
            ->with('success', __('Viewer Display Access data successfully updated.'));
    }

    /**
     * Toggle active status of a guest display credential.
     */
    public function toggleActive(GuestDisplay $guestDisplay): RedirectResponse
    {
        $this->ensureAdmin();

        $isCurrentlyAccessible = $guestDisplay->isAccessible();

        if ($isCurrentlyAccessible) {
            $guestDisplay->update([
                'is_active' => false,
            ]);

            $statusMessage = __('Akses viewer untuk :name telah dinonaktifkan.', [
                'name' => $guestDisplay->name,
            ]);
        } else {
            $updates = ['is_active' => true];

            if ($guestDisplay->isExpired()) {
                // If currently expired, automatically extend validity by 7 days so activation actually works
                $newValidDate = Carbon::today()->addDays(7);
                $updates['valid_until'] = $newValidDate;

                $statusMessage = __('Akses viewer untuk :name telah diaktifkan kembali dan masa berlaku otomatis diperpanjang 7 hari (hingga :date).', [
                    'name' => $guestDisplay->name,
                    'date' => $newValidDate->translatedFormat('d F Y'),
                ]);
            } else {
                $statusMessage = __('Akses viewer untuk :name telah diaktifkan.', [
                    'name' => $guestDisplay->name,
                ]);
            }

            $guestDisplay->update($updates);
        }

        return back()->with('success', $statusMessage);
    }

    /**
     * Delete a guest display credential.
     */
    public function destroy(GuestDisplay $guestDisplay): RedirectResponse
    {
        $this->ensureAdmin();

        $guestDisplay->delete();

        return redirect()->route('display-access.index')
            ->with('success', __('Viewer Display Access successfully deleted.'));
    }

    // ==========================================
    // PUBLIC / GUEST VERIFICATION & VIEW ACTIONS
    // ==========================================

    /**
     * Show access page (PIN verification or Expired status).
     */
    public function showAccess(string $token): View|RedirectResponse
    {
        $guest = GuestDisplay::where('access_token', $token)->first();

        if (! $guest) {
            abort(404, 'Tautan akses display tidak valid atau tidak ditemukan.');
        }

        session(['guest_display_token' => $token]);

        // If inactive or expired
        if (! $guest->is_active || $guest->isExpired()) {
            return view('guest-display.expired', [
                'guest' => $guest,
                'isInactive' => ! $guest->is_active,
            ]);
        }

        // If already authenticated in session, go straight to report view
        if (session("guest_display_auth_{$token}")) {
            return redirect()->route('display.access.view', ['token' => $token]);
        }

        $throttleKey = 'viewer-verify-pin:'.request()->ip();
        $isBlocked = RateLimiter::tooManyAttempts($throttleKey, 3);

        return view('guest-display.verify-pin', compact('guest', 'isBlocked'));
    }

    /**
     * Verify PIN entered by guest.
     */
    public function verifyPin(Request $request, string $token): RedirectResponse
    {
        $guest = GuestDisplay::where('access_token', $token)->firstOrFail();

        // Check active and expiration
        if (! $guest->is_active || $guest->isExpired()) {
            return redirect()->route('display.access', ['token' => $token]);
        }

        // Rate Limiter: Maksimal 3 kali percobaan salah per IP perangkat, blokir 30 menit (1800 detik)
        $throttleKey = 'viewer-verify-pin:'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return back()->withErrors([
                'pin' => 'Akses verifikasi PIN diblokir karena telah mencapai batas maksimal percobaan salah dari perangkat ini.',
            ]);
        }

        $request->validate([
            'pin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
        ], [
            'pin.required' => 'Silakan masukkan 4 digit PIN akses Anda.',
            'pin.size' => 'PIN harus berupa 4 digit angka.',
            'pin.regex' => 'PIN harus berupa 4 digit angka.',
        ]);

        if (trim($request->input('pin')) !== trim($guest->pin)) {
            RateLimiter::hit($throttleKey, 1800); // Blokir 30 menit (1800 detik)

            if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
                return back()->withErrors([
                    'pin' => 'Akses verifikasi PIN diblokir karena telah mencapai batas maksimal percobaan salah dari perangkat ini.',
                ]);
            }

            $remaining = RateLimiter::remaining($throttleKey, 3);

            return back()->withErrors([
                'pin' => "PIN yang Anda masukkan salah. Sisa kesempatan mencoba: {$remaining} kali.",
            ])->withInput();
        }

        // Authentication successful: reset rate limiter IP
        RateLimiter::clear($throttleKey);

        $guest->update([
            'last_accessed_at' => Carbon::now(),
        ]);

        session([
            'guest_display_token' => $token,
            "guest_display_auth_{$token}" => true,
        ]);

        return redirect()->route('display.access.view', ['token' => $token]);
    }

    /**
     * View the display report once authenticated.
     */
    public function viewReport(Request $request, string $token): View|RedirectResponse|JsonResponse
    {
        $guest = GuestDisplay::where('access_token', $token)->firstOrFail();

        if (! $guest->is_active || $guest->isExpired()) {
            session()->forget("guest_display_auth_{$token}");

            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Akses telah kedaluwarsa atau dinonaktifkan.',
                    'redirect' => route('display.access', ['token' => $token]),
                ], 403);
            }

            return redirect()->route('display.access', ['token' => $token]);
        }

        if (! session("guest_display_auth_{$token}")) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Sesi belum terverifikasi.',
                    'redirect' => route('display.access', ['token' => $token]),
                ], 401);
            }

            return redirect()->route('display.access', ['token' => $token]);
        }

        // Retrieve dashboard data from DashboardController
        $dashboardController = app(DashboardController::class);

        if ($request->ajax()) {
            return $dashboardController->index($request);
        }

        $data = $dashboardController->getDashboardData($request);

        // Append guest context
        $data['guestDisplay'] = $guest;

        return view('dashboard.display-report', $data);
    }

    /**
     * Logout guest from current display session.
     */
    public function logout(string $token): RedirectResponse
    {
        session()->forget("guest_display_auth_{$token}");

        return redirect()->route('display.access', ['token' => $token])
            ->with('success', __('You have successfully logged out of the Display Report session.'));
    }
}
