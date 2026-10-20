<?php

namespace App\Http\Controllers;

use App\Mail\InspectionAssigned;
use App\Models\Apar;
use App\Models\Gedung;
use App\Models\JadwalInspeksi;
use App\Models\JenisApar;
use App\Models\KapasitasApar;
use App\Models\Lokasi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InspeksiController extends Controller
{
    private function getPertanyaan()
    {
        return [
            __('Apakah APAR tersebut berada dalam lokasi yang mudah diakses dan terlihat dengan jelas?'),
            __('Apakah penunjuk tekanan pada APAR menunjukkan tekanan yang sesuai?'),
            __('Apakah segel keselamatan pada APAR terjaga dan tidak rusak?'),
            __('Apakah tabung APAR dalam kondisi baik dan tidak terdapat kerusakan fisik?'),
            __('Apakah spindel pengatur aliran pada APAR berfungsi dengan baik?'),
            __('Apakah tuas pemadam pada APAR dapat dioperasikan dengan mudah dan bebas dari kebocoran?'),
            __('Apakah nozzle atau alat semprot pada APAR tidak tersumbat atau rusak?'),
            __('Apakah selang pemadam pada APAR tidak terdapat kerusakan, sobek, atau kebocoran?'),
            __('Apakah label instruksi penggunaan APAR masih terpasang dan mudah dibaca?'),
            __('Apakah tanggal terakhir inspeksi APAR telah dicatat dan sesuai dengan jadwal inspeksi yang ditetapkan?'),
            __('Apakah APAR tersebut dilengkapi dengan segala perlengkapan tambahan yang diperlukan, seperti penyangga dinding atau bracket pemasangan?'),
            __('Apakah petunjuk penggunaan APAR dan tanda peringatan bahaya terkait penggunaan APAR tersedia dan mudah diakses?'),
            __('Apakah petugas yang bertanggung jawab terhadap APAR terlatih dalam penggunaan dan pemeliharaan APAR?'),
            __('Apakah daerah sekitar APAR bebas dari bahan yang mudah terbakar atau bahan yang dapat menghambat akses ke APAR?'),
            __('Apakah APAR tersebut telah diuji atau dirakit kembali setelah digunakan sebelumnya?'),

            // 10 Item Pemeriksaan Fisik (Langkah 1)
            __('Jenis APAR'),
            __('Kapasitas'),
            __('Tanggal isi ulang terakhir'),
            __('Tanggal masa berlaku'),
            __('Segel keselamatan'),
            __('Karat pada tabung'),
            __('Kepadatan isi dalam tabung'),
            __('Nozzle'),
            __('Pin pengaman'),
            __('Lokasi APAR'),
        ];
    }

    public function index(Request $request)
    {
        $currentMonth = (int) $request->input('month', now()->month);
        $currentYear = (int) $request->input('year', now()->year);

        $query = Gedung::query();

        // Jika Staff, hanya ambil gedung yang ditugaskan ke dia
        if (auth()->user()->role === 'Staff') {
            $query->whereHas('users', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        $gedungs = $query->with([
            'lokasi.apar',
            'lokasi.apar.inspeksis' => function ($query) use ($currentMonth, $currentYear) {
                $query->whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear)
                    ->latest();
            },
            'lokasi.apar.inspeksis.user',
        ])->get();

        // Filter out empty gedungs and lokasis for cleaner UI
        $gedungs = $gedungs->filter(function ($gedung) {
            $gedung->lokasi = $gedung->lokasi->filter(function ($lokasi) {
                return $lokasi->apar->isNotEmpty();
            });

            return $gedung->lokasi->isNotEmpty();
        });

        $totalApar = 0;
        $selesai = 0;

        foreach ($gedungs as $gedung) {
            foreach ($gedung->lokasi as $lokasi) {
                foreach ($lokasi->apar as $apar) {
                    $totalApar++;
                    if ($apar->inspeksis->isNotEmpty()) {
                        $selesai++;
                    }
                }
            }
        }

        $menunggu = $totalApar - $selesai;
        $pertanyaan = $this->getPertanyaan();

        $jadwalsQuery = JadwalInspeksi::with(['gedung', 'lokasi', 'user'])
            ->whereMonth('tanggal_inspeksi', $currentMonth)
            ->whereYear('tanggal_inspeksi', $currentYear)
            ->orderBy('tanggal_inspeksi', 'asc');
        $jadwals = $jadwalsQuery->get();
        $users = User::with('gedungs')->get();

        // Hitung progres untuk jadwal asli (Ad-Hoc)
        foreach ($jadwals as $jadwal) {
            $aparsInSchedule = $jadwal->tipe_area === 'gedung'
                ? Apar::whereHas('lokasi', fn ($q) => $q->where('gedung_id', $jadwal->gedung_id))->get()
                : Apar::where('lokasi_id', $jadwal->lokasi_id)->get();

            $jadwalMonth = Carbon::parse($jadwal->tanggal_inspeksi)->month;
            $jadwalYear = Carbon::parse($jadwal->tanggal_inspeksi)->year;

            $totalAparInArea = $aparsInSchedule->count();
            $inspectedCount = 0;

            foreach ($aparsInSchedule as $a) {
                if ($a->inspeksis()->whereMonth('created_at', $jadwalMonth)->whereYear('created_at', $jadwalYear)->exists()) {
                    $inspectedCount++;
                }
            }

            $jadwal->total_apar = $totalAparInArea;
            $jadwal->inspected_apar = $inspectedCount;

            if ($totalAparInArea > 0 && $inspectedCount === $totalAparInArea) {
                $jadwal->status = 'selesai';
            } elseif ($inspectedCount > 0) {
                $jadwal->status = 'proses';
            }
        }

        // Gabungkan jadwal rutin dari profil Staff ke dalam daftar jadwal
        foreach ($users as $user) {
            if ($user->role === 'Staff' && $user->jadwal_rutin_tanggal) {
                $maxDays = Carbon::create($currentYear, $currentMonth, 1)->daysInMonth;
                $tgl = min($user->jadwal_rutin_tanggal, $maxDays);
                $tanggalRutin = Carbon::create($currentYear, $currentMonth, $tgl)->format('Y-m-d');

                foreach ($user->gedungs as $gedung) {
                    $mockJadwal = new JadwalInspeksi([
                        'jenis_jadwal' => 'Inspeksi Rutin Bulanan',
                        'tanggal_inspeksi' => $tanggalRutin,
                        'tipe_area' => 'gedung',
                        'gedung_id' => $gedung->id,
                        'user_id' => $user->id,
                        'catatan_tambahan' => 'Jadwal rutin bulanan otomatis',
                        'status' => 'menunggu',
                    ]);
                    $mockJadwal->id = 'rutin_'.$user->id.'_'.$gedung->id;
                    $mockJadwal->setRelation('gedung', $gedung);
                    $mockJadwal->setRelation('user', $user);
                    $mockJadwal->is_rutin_virtual = true;

                    // Cek progres
                    $aparsInGedung = $gedung->lokasi->flatMap->apar;
                    $totalAparInArea = $aparsInGedung->count();
                    $inspectedCount = 0;

                    foreach ($aparsInGedung as $a) {
                        if ($a->inspeksis()->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->exists()) {
                            $inspectedCount++;
                        }
                    }

                    $mockJadwal->total_apar = $totalAparInArea;
                    $mockJadwal->inspected_apar = $inspectedCount;

                    if ($totalAparInArea > 0 && $inspectedCount === $totalAparInArea) {
                        $mockJadwal->status = 'selesai';
                    } elseif ($inspectedCount > 0) {
                        $mockJadwal->status = 'proses';
                    }

                    $jadwals->push($mockJadwal);
                }
            }
        }

        // Urutkan ulang setelah digabung
        $jadwals = $jadwals->sortBy('tanggal_inspeksi')->values();

        return view('inspection-schedule.index', compact('gedungs', 'totalApar', 'selesai', 'menunggu', 'pertanyaan', 'jadwals', 'users', 'currentMonth', 'currentYear'));
    }

    public function exportChecklist(Request $request)
    {
        $request->validate([
            'gedung_id' => 'required|exists:gedung,id',
        ]);

        $gedungId = $request->gedung_id;
        $gedung = Gedung::findOrFail($gedungId);

        $query = Apar::with(['lokasi.gedung', 'jenis', 'kapasitas'])
            ->whereHas('lokasi', function ($q) use ($gedungId) {
                $q->where('gedung_id', $gedungId);
            });

        if (auth()->user()->role === 'Staff') {
            $query->whereHas('lokasi.gedung.users', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        $apars = $query->orderBy('kode', 'asc')->get();

        $originalLocale = app()->getLocale();
        app()->setLocale('id');
        $pertanyaan = $this->getPertanyaan();
        app()->setLocale($originalLocale);

        $pdf = Pdf::loadView('inspeksi.pdf_checklist', compact('apars', 'pertanyaan'))->setPaper('a4', 'landscape');

        $safeGedungName = preg_replace('/[^A-Za-z0-9\-]/', '_', $gedung->nama);

        return $pdf->stream('Checklist_'.$safeGedungName.'_'.date('Ymd').'.pdf');
    }

    public function pedoman(Apar $apar)
    {
        // Set penanda bahwa user sedang memulai alur inspeksi baru
        session()->put('active_inspection_'.$apar->id, true);

        return view('inspeksi.pedoman', compact('apar'));
    }

    public function create(Request $request, Apar $apar)
    {
        // Mengecek apakah ada sesi inspeksi aktif (di-set dari halaman pedoman)
        // Jika tidak ada, berarti pengguna menekan "Kembali" setelah menyelesaikan inspeksi
        if (! session('active_inspection_'.$apar->id)) {
            return redirect()->route('inspeksi.sukses', ['apar' => $apar->id, 'source' => $request->query('source')])
                ->with('error', 'Sesi form telah selesai. Silakan scan ulang jika ingin melakukan inspeksi kembali.');
        }

        $pertanyaan = $this->getPertanyaan();
        $gedungs = Gedung::with('lokasi')->get();
        $jenisApars = JenisApar::all();
        $kapasitasApars = KapasitasApar::all();

        $backUrl = route('inspeksi.pedoman', $apar->id);
        if ($request->query('source') === 'schedule') {
            $backUrl .= '?source=schedule';
        }

        return view('inspeksi.mulai', compact('apar', 'pertanyaan', 'gedungs', 'jenisApars', 'kapasitasApars', 'backUrl'));
    }

    public function store(Request $request, Apar $apar)
    {
        // KUNCI: Mencegah submit ulang jika user berhasil back ke halaman form dari cache browser
        if (! session('active_inspection_'.$apar->id)) {
            return redirect()->route('inspeksi.sukses', ['apar' => $apar->id, 'source' => $request->query('source')])
                ->with('error', 'Sesi form telah selesai dan terkunci. Form tidak dapat disubmit ulang.');
        }

        $isSystem = in_array($request->query('source') ?? $request->source, ['schedule', 'system']);
        $pesanFoto = $isSystem
            ? 'Harap lampirkan foto kondisi APAR saat inspeksi berlangsung.'
            : 'Ambil foto kondisi APAR secara langsung dari lokasi menggunakan kamera.';

        $request->validate([
            'checklist' => 'required|array|size:25',
            'checklist.*.jawaban' => 'required|in:ya,tidak,ada,tidak ada',
            'checklist.*.keterangan' => 'nullable|string',
            'status' => 'required|string',
            'catatan_tambahan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB
            'foto_base64' => 'required|string',
            'qty' => 'required|integer|min:0',
            'tgl_kedaluwarsa' => 'required|date',
            'tgl_isi_ulang' => 'nullable|date',
            'jenis_id' => 'required|exists:jenis_apar,id',
            'kapasitas_id' => 'required|exists:kapasitas_apar,id',
        ], [
            'foto_base64.required' => __($pesanFoto),
        ]);

        $updateData = [
            'qty' => $request->qty,
            'tgl_kedaluwarsa' => $request->tgl_kedaluwarsa,
            'tgl_isi_ulang' => $request->tgl_isi_ulang ?? $apar->tgl_isi_ulang,
            'pic_id' => auth()->id(),
            'jenis_id' => $request->jenis_id,
            'kapasitas_id' => $request->kapasitas_id,
        ];

        if ($request->hasFile('foto')) {
            if ($apar->foto && Storage::disk('public')->exists($apar->foto)) {
                Storage::disk('public')->delete($apar->foto);
            }
            $updateData['foto'] = $request->file('foto')->store('foto-apar', 'public');
        } elseif ($request->filled('foto_base64')) {
            if ($apar->foto && Storage::disk('public')->exists($apar->foto)) {
                Storage::disk('public')->delete($apar->foto);
            }
            $image_parts = explode(';base64,', $request->foto_base64);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1] ?? 'jpeg';
            $image_base64 = base64_decode($image_parts[1]);
            $path = 'foto-apar/'.uniqid().'.'.$image_type;
            Storage::disk('public')->put($path, $image_base64);
            $updateData['foto'] = $path;
        }

        // Jika Lokasi APAR (index 24) "Tidak Sesuai", update master data lokasi
        if (isset($request->checklist[24]) && $request->checklist[24]['jawaban'] === 'tidak ada' && ! empty($request->checklist[24]['keterangan'])) {
            $newLokasiName = trim($request->checklist[24]['keterangan']);
            $lokasi = Lokasi::firstOrCreate([
                'nama' => $newLokasiName,
                'gedung_id' => $apar->lokasi->gedung_id,
            ]);
            $updateData['lokasi_id'] = $lokasi->id;
        }

        // Jalankan update hanya satu kali agar history tercatat dalam 1 log
        $apar->update($updateData);

        $status = $request->status;
        if ((int) $request->qty <= 0) {
            $status = 'isi_ulang';
        }

        $apar->inspeksis()->create([
            'user_id' => auth()->id(),
            'checklist' => $request->checklist,
            'status' => $status,
            'catatan_tambahan' => $request->catatan_tambahan,
        ]);

        // Check if related JadwalInspeksi should be marked as selesai
        $jadwals = JadwalInspeksi::where('status', 'menunggu')
            ->where(function ($q) use ($apar) {
                $q->where(function ($qGedung) use ($apar) {
                    $qGedung->where('tipe_area', 'gedung')->where('gedung_id', $apar->lokasi->gedung_id);
                })->orWhere(function ($qLokasi) use ($apar) {
                    $qLokasi->where('tipe_area', 'lokasi')->where('lokasi_id', $apar->lokasi_id);
                });
            })->get();

        foreach ($jadwals as $jadwal) {
            $aparsInSchedule = $jadwal->tipe_area === 'gedung'
                ? Apar::whereHas('lokasi', fn ($q) => $q->where('gedung_id', $jadwal->gedung_id))->get()
                : Apar::where('lokasi_id', $jadwal->lokasi_id)->get();

            $allInspected = $aparsInSchedule->every(function ($a) use ($jadwal) {
                return $a->inspeksis()
                    ->whereMonth('created_at', Carbon::parse($jadwal->tanggal_inspeksi)->month)
                    ->whereYear('created_at', Carbon::parse($jadwal->tanggal_inspeksi)->year)
                    ->exists();
            });

            if ($allInspected && $aparsInSchedule->isNotEmpty()) {
                $jadwal->update(['status' => 'selesai']);
            }
        }

        // Hapus penanda sesi aktif agar jika memencet kembali (back) tidak bisa masuk form lagi
        session()->forget('active_inspection_'.$apar->id);

        // KUNCI KREDENSIAL: Logout paksa jika ini dari Direct Scan (agar kredensial tidak nyangkut)
        if (! $isSystem) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('inspeksi.sukses', ['apar' => $apar->id, 'source' => $request->source]);
    }

    public function search(Request $request)
    {
        $request->validate(['kode' => 'required|string']);
        $apar = Apar::where('kode', $request->kode)->first();

        if (! $apar) {
            return back()->withErrors(['kode' => 'APAR dengan ID tersebut tidak ditemukan.']);
        }

        return redirect()->route('inspeksi.pedoman', ['apar' => $apar->id, 'source' => $request->source]);
    }

    public function sukses(Request $request, Apar $apar)
    {
        $nextApar = null;
        if ($request->source === 'schedule') {
            $currentMonth = now()->month;
            $currentYear = now()->year;

            // Cari APAR selanjutnya yang belum diinspeksi di gedung yang sama, dengan ID > ID saat ini
            $nextApar = Apar::whereHas('lokasi', function ($q) use ($apar) {
                $q->where('gedung_id', $apar->lokasi->gedung_id);
            })->whereDoesntHave('inspeksis', function ($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear);
            })->where('id', '>', $apar->id)->orderBy('id', 'asc')->first();

            // Jika tidak ada yang ID-nya lebih besar, cari dari awal (ID yang lebih kecil) di gedung yang sama
            if (! $nextApar) {
                $nextApar = Apar::whereHas('lokasi', function ($q) use ($apar) {
                    $q->where('gedung_id', $apar->lokasi->gedung_id);
                })->whereDoesntHave('inspeksis', function ($q) use ($currentMonth, $currentYear) {
                    $q->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear);
                })->where('id', '!=', $apar->id)->orderBy('id', 'asc')->first();
            }
        }

        $allApars = Apar::with(['lokasi.gedung'])->select('id', 'kode', 'lokasi_id')->get()->map(function ($a) {
            return [
                'kode' => $a->kode,
                'lokasi' => $a->lokasi->nama ?? '-',
                'gedung' => $a->lokasi->gedung->nama ?? '-',
            ];
        });

        return view('inspeksi.sukses', compact('apar', 'nextApar', 'allApars'));
    }

    public function storeJadwal(Request $request)
    {
        $request->validate([
            'jenis_jadwal' => 'required|string',
            'tanggal' => 'required|date',
            'areas' => 'required|array|min:1',
            'user_id' => 'required|exists:users,id',
            'catatan_tambahan' => 'nullable|string',
        ]);

        $jadwals = collect();

        foreach ($request->areas as $area) {
            $isGedung = str_starts_with($area, 'g_');
            $id = substr($area, 2);

            $jadwal = JadwalInspeksi::create([
                'jenis_jadwal' => $request->jenis_jadwal,
                'tanggal_inspeksi' => $request->tanggal,
                'tipe_area' => $isGedung ? 'gedung' : 'lokasi',
                'gedung_id' => $isGedung ? $id : null,
                'lokasi_id' => ! $isGedung ? $id : null,
                'user_id' => $request->user_id,
                'catatan_tambahan' => $request->catatan_tambahan,
                'status' => 'menunggu',
            ]);

            $jadwals->push($jadwal);
        }

        $user = User::find($request->user_id);
        if ($user && $user->email && $jadwals->isNotEmpty()) {
            Mail::to($user->email)->send(new InspectionAssigned($user, $jadwals, $user->pin));
        }

        return redirect('/inspection-schedule')->with('success', __('Jadwal inspeksi berhasil dibuat dan notifikasi email telah dikirim!'));
    }

    public function updateJadwal(Request $request, JadwalInspeksi $jadwal)
    {
        $request->validate([
            'jenis_jadwal' => 'required|string',
            'tanggal' => 'required|date',
            'tipe_area' => 'required|in:gedung,lokasi',
            'gedung_id' => 'required_if:tipe_area,gedung',
            'lokasi_id' => 'required_if:tipe_area,lokasi',
            'user_id' => 'required|exists:users,id',
            'catatan_tambahan' => 'nullable|string',
        ]);

        $jadwal->update([
            'jenis_jadwal' => $request->jenis_jadwal,
            'tanggal_inspeksi' => $request->tanggal,
            'tipe_area' => $request->tipe_area,
            'gedung_id' => $request->tipe_area === 'gedung' ? $request->gedung_id : null,
            'lokasi_id' => $request->tipe_area === 'lokasi' ? $request->lokasi_id : null,
            'user_id' => $request->user_id,
            'catatan_tambahan' => $request->catatan_tambahan,
        ]);

        return redirect('/inspection-schedule')->with('success', __('Jadwal inspeksi berhasil diperbarui!'));
    }

    public function destroyJadwal($id)
    {
        $jadwal = JadwalInspeksi::find($id);

        if ($jadwal) {
            $jadwal->delete();

            return redirect('/inspection-schedule')->with('success', __('Jadwal inspeksi berhasil dihapus!'));
        }

        return redirect('/inspection-schedule');
    }

    public function reverseGeocode(Request $request)
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');

        if (! $lat || ! $lon) {
            return response()->json(['address' => null], 400);
        }

        $cacheKey = 'geocode_'.round((float) $lat, 4).'_'.round((float) $lon, 4);

        $address = Cache::remember($cacheKey, 86400, function () use ($lat, $lon) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'APAR-Monitoring-System/1.0 (app@example.com)',
                ])->timeout(3)->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lon,
                    'zoom' => 18,
                    'addressdetails' => 1,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // Buat format alamat yang ringkas dan informatif
                    $addr = $data['address'] ?? [];
                    $parts = [];

                    $buildingOrPoi = $addr['amenity'] ?? $addr['building'] ?? $addr['industrial'] ?? $addr['commercial'] ?? null;
                    if ($buildingOrPoi) {
                        $parts[] = $buildingOrPoi;
                    }

                    $road = $addr['road'] ?? $addr['suburb'] ?? $addr['village'] ?? null;
                    if ($road) {
                        $parts[] = $road;
                    }

                    $city = $addr['city'] ?? $addr['county'] ?? $addr['regency'] ?? $addr['town'] ?? null;
                    if ($city) {
                        $parts[] = $city;
                    }

                    $state = $addr['state'] ?? null;
                    if ($state && count($parts) < 3) {
                        $parts[] = $state;
                    }

                    if (! empty($parts)) {
                        return implode(', ', array_unique($parts));
                    }

                    return $data['display_name'] ?? null;
                }
            } catch (\Exception $e) {
                // Ignore error, return null
            }

            return null;
        });

        return response()->json(['address' => $address]);
    }
}
