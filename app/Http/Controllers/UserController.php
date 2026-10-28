<?php

namespace App\Http\Controllers;

use App\Mail\NewUserRegistered;
use App\Mail\UserScheduleUpdated;
use App\Models\Gedung;
use App\Models\GuestDisplay;
use App\Models\Lokasi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->with(['gedungs', 'lokasis.gedung'])->latest()->paginate(10)->withQueryString();
        $roles = User::select('role')->whereNotNull('role')->distinct()->pluck('role');
        $gedungs = Gedung::whereHas('lokasi.apar')
            ->with([
                'users:id,name',
                'lokasi' => function ($query) {
                    $query->has('apar')
                        ->withCount('apar')
                        ->with('users:id,name')
                        ->orderBy('nama');
                },
            ])
            ->orderBy('nama')
            ->get();
        $allUsersForValidation = User::select('id', 'employee_id', 'email')->get();

        return view('users.index', compact('users', 'roles', 'gedungs', 'allUsersForValidation'));
    }

    public function exportPdf(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->with(['gedungs', 'lokasis.gedung'])->latest()->get();

        $pdf = Pdf::loadView('users.pdf', compact('users'))->setPaper('a4', 'portrait');

        return $pdf->stream('Data_User_'.date('Ymd_His').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->with(['gedungs', 'lokasis.gedung'])->latest()->get();

        $filename = 'Data_User_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'No',
            'User ID / ID Pengguna',
            'Name / Nama',
            'Email',
            'Building / Gedung',
            'Role / Peran',
            'Inspection Schedule / Jadwal Inspeksi',
        ];

        $callback = function () use ($users, $columns) {
            $file = fopen('php://output', 'w');
            // add BOM to fix UTF-8 in Excel
            fwrite($file, $bom = (chr(0xEF).chr(0xBB).chr(0xBF)));
            fputcsv($file, $columns);

            foreach ($users as $index => $user) {
                $areas = [];
                foreach ($user->gedungs as $g) {
                    $areas[] = $g->nama;
                }
                foreach ($user->lokasis as $l) {
                    $areas[] = ($l->gedung->nama ?? '').': '.($l->nomor_ruangan ? '['.$l->nomor_ruangan.'] ' : '').$l->nama;
                }
                $gedungsStr = implode(', ', $areas);

                $row = [
                    $index + 1,
                    $user->employee_id ?? 'n/a',
                    $user->name,
                    $user->email,
                    $gedungsStr ?: 'n/a',
                    $user->role,
                    $user->jadwal_rutin_tanggal ? 'Tanggal '.$user->jadwal_rutin_tanggal : 'n/a',
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function previewExcel(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->with(['gedungs', 'lokasis.gedung'])->latest()->get();

        $columns = [
            'No',
            'User ID / ID Pengguna',
            'Name / Nama',
            'Email',
            'Building / Gedung',
            'Role / Peran',
            'Inspection Schedule / Jadwal Inspeksi',
        ];
        $rows = [];

        foreach ($users as $index => $user) {
            $areas = [];
            foreach ($user->gedungs as $g) {
                $areas[] = $g->nama;
            }
            foreach ($user->lokasis as $l) {
                $areas[] = ($l->gedung->nama ?? '').': '.($l->nomor_ruangan ? '['.$l->nomor_ruangan.'] ' : '').$l->nama;
            }
            $gedungsStr = implode(', ', $areas);

            $rows[] = [
                $index + 1,
                $user->employee_id ?? 'n/a',
                $user->name,
                $user->email,
                $gedungsStr ?: 'n/a',
                $user->role,
                $user->jadwal_rutin_tanggal ? 'Tanggal '.$user->jadwal_rutin_tanggal : 'n/a',
            ];
        }

        return response()->json([
            'headers' => $columns,
            'rows' => $rows,
        ]);
    }

    public function checkPin(Request $request)
    {
        $pin = $request->query('pin');
        $userId = $request->query('user_id');

        if (! $pin || strlen($pin) !== 4 || ! ctype_digit($pin)) {
            return response()->json(['safe' => true]);
        }

        if (GuestDisplay::where('pin', $pin)->exists()) {
            return response()->json([
                'safe' => false,
                'message' => __('PIN yang anda buat berbahaya dan tidak aman'),
            ]);
        }

        $allUsers = User::whereNotNull('pin')
            ->when($userId, fn ($q) => $q->where('id', '!=', $userId))
            ->get();

        $exists = $allUsers->contains(function ($u) use ($pin) {
            return $u->matchesPin($pin);
        });

        return response()->json([
            'safe' => ! $exists,
            'message' => $exists ? __('PIN yang anda buat berbahaya dan tidak aman') : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|max:50|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:EHSS,Staff',
            'pin' => ['nullable', 'string', 'size:4', 'regex:/^[0-9]+$/', 'unique:users,pin'],
            'jadwal_rutin_tanggal' => 'nullable|integer|min:1|max:31',
            'gedungs' => 'nullable|array',
            'gedungs.*' => 'exists:gedung,id',
            'lokasis' => 'nullable|array',
            'lokasis.*' => 'exists:lokasi,id',
        ], [
            'employee_id.required' => __('User ID field is required.'),
            'employee_id.unique' => __('This User ID is already registered. Please use a different User ID.'),
            'email.required' => __('Email field is required.'),
            'email.unique' => __('This Email is already registered. Please use a different Email.'),
            'email.email' => __('The email format is invalid.'),
            'pin.unique' => __('PIN yang anda buat berbahaya dan tidak aman'),
            'pin.size' => __('PIN inspeksi harus terdiri dari 4 digit angka.'),
            'pin.regex' => __('PIN inspeksi hanya boleh berisi angka (0-9).'),
        ]);

        $allUsers = User::whereNotNull('pin')->get();

        if ($request->filled('pin')) {
            $pin = $request->pin;
            $existsInGuest = GuestDisplay::where('pin', $pin)->exists();
            $existsInUsers = $allUsers->contains(function ($u) use ($pin) {
                return $u->matchesPin($pin);
            });

            if ($existsInGuest || $existsInUsers) {
                return redirect()->back()->withErrors(['pin' => __('PIN yang anda buat berbahaya dan tidak aman')])->withInput();
            }
        } else {
            // Auto-generate unique 4-digit PIN
            do {
                $pin = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                $exists = GuestDisplay::where('pin', $pin)->exists() || $allUsers->contains(function ($u) use ($pin) {
                    return $u->matchesPin($pin);
                });
            } while ($exists);
        }

        // Default password for all users (will be changed on setup)
        $password = Str::random(16);

        $user = User::create([
            'employee_id' => $request->employee_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => $request->role,
            'pin' => $pin,
            'jadwal_rutin_tanggal' => $request->role === 'Staff' ? $request->jadwal_rutin_tanggal : null,
        ]);

        if ($request->role === 'Staff') {
            if ($request->has('gedungs')) {
                $user->gedungs()->sync($request->gedungs);
            }
            if ($request->has('lokasis')) {
                $validLokasis = Lokasi::has('apar')->whereIn('id', $request->lokasis)->pluck('id');
                $user->lokasis()->sync($validLokasis);
            }
        }

        // Create signed setup URL
        $setupUrl = URL::temporarySignedRoute(
            'setup-password',
            now()->addDays(3),
            ['user' => $user->id]
        );

        // Send Email
        $user->withoutRelations()->load(['gedungs', 'lokasis.gedung']);
        Mail::to($user->email)->send(new NewUserRegistered($user, $setupUrl, $pin));

        return redirect()->back()->with('success', __('User berhasil ditambahkan! Email setup telah dikirim.'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|string|in:EHSS,Staff',
            'pin' => ['nullable', 'string', 'size:4', 'regex:/^[0-9]+$/', Rule::unique('users', 'pin')->ignore($user->id)],
            'jadwal_rutin_tanggal' => 'nullable|integer|min:1|max:31',
            'gedungs' => 'nullable|array',
            'gedungs.*' => 'exists:gedung,id',
            'lokasis' => 'nullable|array',
            'lokasis.*' => 'exists:lokasi,id',
        ], [
            'employee_id.required' => __('User ID field is required.'),
            'employee_id.unique' => __('This User ID is already registered. Please use a different User ID.'),
            'email.required' => __('Email field is required.'),
            'email.unique' => __('This Email is already registered. Please use a different Email.'),
            'email.email' => __('The email format is invalid.'),
            'pin.unique' => __('PIN yang anda buat berbahaya dan tidak aman'),
            'pin.size' => __('PIN inspeksi harus terdiri dari 4 digit angka.'),
            'pin.regex' => __('PIN inspeksi hanya boleh berisi angka (0-9).'),
        ]);

        $data = [
            'employee_id' => $request->employee_id,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'jadwal_rutin_tanggal' => $request->role === 'Staff' ? $request->jadwal_rutin_tanggal : null,
        ];

        $plainPin = null;
        if ($request->filled('pin')) {
            $pin = $request->pin;
            $existsInGuest = GuestDisplay::where('pin', $pin)->exists();
            $existsInUsers = User::whereNotNull('pin')->where('id', '!=', $user->id)->get()->contains(function ($u) use ($pin) {
                return $u->matchesPin($pin);
            });

            if ($existsInGuest || $existsInUsers) {
                return redirect()->back()->withErrors(['pin' => __('PIN yang anda buat berbahaya dan tidak aman')])->withInput();
            }

            $data['pin'] = $pin;
            $plainPin = $pin;
        }

        $user->update($data);

        if ($request->role === 'Staff') {
            $user->gedungs()->sync($request->gedungs ?? []);
            $validLokasis = Lokasi::has('apar')->whereIn('id', $request->lokasis ?? [])->pluck('id');
            $user->lokasis()->sync($validLokasis);
        } else {
            $user->gedungs()->detach();
            $user->lokasis()->detach();
        }

        // Kirim email notifikasi ke user setelah perubahan profil/jadwal/gedung
        $user->withoutRelations()->load(['gedungs', 'lokasis.gedung']);
        Mail::to($user->email)->send(new UserScheduleUpdated($user, null, $plainPin));

        return redirect()->back()->with('success', __('Data user berhasil diperbarui!'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->back()->with('success', __('User berhasil dihapus!'));
    }
}
