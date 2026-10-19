<?php

namespace App\Http\Controllers;

use App\Mail\NewUserRegistered;
use App\Mail\UserScheduleUpdated;
use App\Models\Gedung;
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

        $users = $query->with('gedungs')->latest()->paginate(10)->withQueryString();
        $roles = User::select('role')->whereNotNull('role')->distinct()->pluck('role');
        $gedungs = Gedung::with('users:id,name')->orderBy('nama')->get();
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

        $users = $query->with('gedungs')->latest()->get();

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

        $users = $query->with('gedungs')->latest()->get();

        $filename = 'Data_User_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [__('No'), __('User ID'), __('Name'), __('Email'), __('Building'), __('Role'), __('Inspection Schedule')];

        $callback = function () use ($users, $columns) {
            $file = fopen('php://output', 'w');
            // add BOM to fix UTF-8 in Excel
            fwrite($file, $bom = (chr(0xEF).chr(0xBB).chr(0xBF)));
            fputcsv($file, $columns);

            foreach ($users as $index => $user) {
                $gedungsStr = $user->gedungs->pluck('nama')->implode(', ');
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

        $users = $query->with('gedungs')->latest()->get();

        $columns = [__('No'), __('User ID'), __('Name'), __('Email'), __('Building'), __('Role'), __('Inspection Schedule')];
        $rows = [];

        foreach ($users as $index => $user) {
            $gedungsStr = $user->gedungs->pluck('nama')->implode(', ');
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

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|max:50|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:EHSS,Staff',
            'jadwal_rutin_tanggal' => 'nullable|integer|min:1|max:31',
            'gedungs' => 'nullable|array',
            'gedungs.*' => 'exists:gedung,id',
        ], [
            'employee_id.required' => __('User ID field is required.'),
            'employee_id.unique' => __('This User ID is already registered. Please use a different User ID.'),
            'email.required' => __('Email field is required.'),
            'email.unique' => __('This Email is already registered. Please use a different Email.'),
            'email.email' => __('The email format is invalid.'),
        ]);

        // Auto-generate unique 4-digit PIN
        $allUsers = User::whereNotNull('pin')->get();
        do {
            $pin = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $exists = $allUsers->contains(function ($u) use ($pin) {
                return Hash::check($pin, $u->pin) || $u->pin === $pin;
            });
        } while ($exists);

        // Default password for all users (will be changed on setup)
        $password = Str::random(16);

        $user = User::create([
            'employee_id' => $request->employee_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => $request->role,
            'pin' => Hash::make($pin),
            'jadwal_rutin_tanggal' => $request->role === 'Staff' ? $request->jadwal_rutin_tanggal : null,
        ]);

        if ($request->role === 'Staff' && $request->has('gedungs')) {
            $user->gedungs()->sync($request->gedungs);
        }

        // Create signed setup URL
        $setupUrl = URL::temporarySignedRoute(
            'setup-password',
            now()->addDays(3),
            ['user' => $user->id]
        );

        // Send Email
        Mail::to($user->email)->send(new NewUserRegistered($user, $setupUrl));

        return redirect()->back()->with('success', __('User berhasil ditambahkan! Email setup telah dikirim.'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|string|in:EHSS,Staff',
            'pin' => ['nullable', 'string', 'size:4', 'regex:/^[0-9]+$/'],
            'jadwal_rutin_tanggal' => 'nullable|integer|min:1|max:31',
            'gedungs' => 'nullable|array',
            'gedungs.*' => 'exists:gedung,id',
        ], [
            'employee_id.required' => __('User ID field is required.'),
            'employee_id.unique' => __('This User ID is already registered. Please use a different User ID.'),
            'email.required' => __('Email field is required.'),
            'email.unique' => __('This Email is already registered. Please use a different Email.'),
            'email.email' => __('The email format is invalid.'),
        ]);

        $data = [
            'employee_id' => $request->employee_id,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'jadwal_rutin_tanggal' => $request->role === 'Staff' ? $request->jadwal_rutin_tanggal : null,
        ];

        if ($request->filled('pin')) {
            $pin = $request->pin;
            $exists = User::whereNotNull('pin')->where('id', '!=', $user->id)->get()->contains(function ($u) use ($pin) {
                return Hash::check($pin, $u->pin) || $u->pin === $pin;
            });

            if ($exists) {
                return redirect()->back()->withErrors(['pin' => __('The PIN has already been taken.')])->withInput();
            }

            $data['pin'] = Hash::make($pin);
        }

        $user->update($data);

        if ($request->role === 'Staff') {
            $user->gedungs()->sync($request->gedungs ?? []);

            // Kirim email notifikasi ke user setelah perubahan profil/jadwal/gedung
            $buildings = Gedung::whereIn('id', $request->gedungs ?? [])->get();
            Mail::to($user->email)->send(new UserScheduleUpdated($user, $buildings));
        } else {
            $user->gedungs()->detach();
        }

        return redirect()->back()->with('success', __('Data user berhasil diperbarui!'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->back()->with('success', __('User berhasil dihapus!'));
    }
}
