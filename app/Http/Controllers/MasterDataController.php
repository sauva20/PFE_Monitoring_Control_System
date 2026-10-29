<?php

namespace App\Http\Controllers;

use App\Models\Apar;
use App\Models\AparCadangan;
use App\Models\Gedung;
use App\Models\JenisApar;
use App\Models\KapasitasApar;
use App\Models\Lokasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $gedungs = Gedung::all();
        $lokasis = Lokasi::with('gedung')->get();
        $jenisApars = JenisApar::all();
        $kapasitasApars = KapasitasApar::all();

        $query = Apar::with(['lokasi.gedung', 'jenis', 'kapasitas', 'latestInspeksi.user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nomor_ruangan', 'like', "%{$search}%")
                    ->orWhereHas('lokasi', function ($q2) use ($search) {
                        $q2->where('nama', 'like', "%{$search}%")
                            ->orWhereHas('gedung', function ($q3) use ($search) {
                                $q3->where('nama', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('pic', function ($q4) use ($search) {
                        $q4->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('jenis', function ($q5) use ($search) {
                        $q5->where('nama', 'like', "%{$search}%");
                    })
                    ->orWhereHas('kapasitas', function ($q6) use ($search) {
                        $q6->where('ukuran', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('gedung_id')) {
            $query->whereHas('lokasi', function ($q) use ($request) {
                $q->where('gedung_id', $request->gedung_id);
            });
        }

        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        if ($request->filled('kapasitas_id')) {
            $query->where('kapasitas_id', $request->kapasitas_id);
        }

        if ($request->filled('jenis_id')) {
            $query->where('jenis_id', $request->jenis_id);
        }

        if ($request->filled('qty_status') && $request->qty_status === 'empty') {
            $query->where('qty', '<=', 0);
        }

        $apars = $query->orderBy('id', 'asc')->paginate(10)->withQueryString();

        $aparCadangans = AparCadangan::with(['gedung', 'jenis', 'kapasitas'])->get();

        $allApars = Apar::with(['jenis', 'kapasitas', 'latestInspeksi'])->get();

        $resumeColumns = [];
        foreach ($allApars as $apar) {
            $jenis = $apar->jenis->nama ?? 'Unknown';
            $kapasitas = $apar->kapasitas->ukuran ?? 'Unknown';

            if (! isset($resumeColumns[$jenis])) {
                $resumeColumns[$jenis] = [];
            }
            if (! in_array($kapasitas, $resumeColumns[$jenis])) {
                $resumeColumns[$jenis][] = $kapasitas;
            }
        }

        // sort capacities logically if possible, or just leave as is
        foreach ($resumeColumns as $j => $caps) {
            sort($resumeColumns[$j]);
        }

        $resumeRows = [
            'APAR Keseluruhan' => fn ($a) => true,
            'APAR Kedaluwarsa' => fn ($a) => $a->tgl_kedaluwarsa && Carbon::parse($a->tgl_kedaluwarsa)->isPast(),
            'Sesuai' => fn ($a) => $a->latestInspeksi && in_array($a->latestInspeksi->status, ['sesuai', 'layak', 'baik']),
            'Tidak Sesuai' => fn ($a) => $a->latestInspeksi && in_array($a->latestInspeksi->status, ['tidak_sesuai', 'perbaikan', 'rusak', 'service', 'tidak_layak']),
            'Perlu Refill' => fn ($a) => $a->latestInspeksi && in_array($a->latestInspeksi->status, ['perlu_refill', 'isi_ulang']),
        ];

        $resumeTable = [];
        foreach ($resumeRows as $rowName => $filterCallback) {
            $rowData = [];
            $rowTotal = 0;
            foreach ($resumeColumns as $jenis => $kapasitasList) {
                foreach ($kapasitasList as $kapasitas) {
                    $count = $allApars->filter(function ($a) use ($jenis, $kapasitas, $filterCallback) {
                        $j = $a->jenis->nama ?? 'Unknown';
                        $k = $a->kapasitas->ukuran ?? 'Unknown';

                        return $j === $jenis && $k === $kapasitas && $filterCallback($a);
                    })->count();
                    $rowData[$jenis][$kapasitas] = $count;
                    $rowTotal += $count;
                }
            }
            $resumeTable[] = [
                'name' => $rowName,
                'data' => $rowData,
                'total' => $rowTotal,
            ];
        }

        $allAparKodes = $allApars->pluck('kode')->filter()->values()->toArray();
        $allAparItems = $allApars->map(function ($apar) {
            preg_match('/\d+$/', $apar->kode ?? '', $matches);

            return [
                'id' => (string) $apar->id,
                'kode' => $apar->kode,
                'number' => $matches[0] ?? '',
            ];
        })->filter(fn ($item) => ! empty($item['number']))->values()->toArray();

        return view('master-data.index', compact('gedungs', 'lokasis', 'jenisApars', 'kapasitasApars', 'apars', 'aparCadangans', 'resumeColumns', 'resumeTable', 'allAparKodes', 'allAparItems'));
    }

    public function exportPdf(Request $request)
    {
        $query = Apar::with(['lokasi.gedung', 'jenis', 'kapasitas', 'latestInspeksi.user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhereHas('lokasi', function ($q2) use ($search) {
                        $q2->where('nama', 'like', "%{$search}%")
                            ->orWhereHas('gedung', function ($q3) use ($search) {
                                $q3->where('nama', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($request->filled('gedung_id')) {
            $query->whereHas('lokasi', function ($q) use ($request) {
                $q->where('gedung_id', $request->gedung_id);
            });
        }

        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        if ($request->filled('kapasitas_id')) {
            $query->where('kapasitas_id', $request->kapasitas_id);
        }

        if ($request->filled('jenis_id')) {
            $query->where('jenis_id', $request->jenis_id);
        }

        $apars = $query->orderBy('id', 'asc')->get();

        $aparCadangans = AparCadangan::with(['gedung', 'jenis', 'kapasitas'])->get();

        $resumeColumns = [];
        foreach ($apars as $apar) {
            $jenis = $apar->jenis->nama ?? 'Unknown';
            $kapasitas = $apar->kapasitas->ukuran ?? 'Unknown';

            if (! isset($resumeColumns[$jenis])) {
                $resumeColumns[$jenis] = [];
            }
            if (! in_array($kapasitas, $resumeColumns[$jenis])) {
                $resumeColumns[$jenis][] = $kapasitas;
            }
        }

        foreach ($resumeColumns as $j => $caps) {
            sort($resumeColumns[$j]);
        }

        $resumeRows = [
            'APAR Keseluruhan' => fn ($a) => true,
            'APAR Kedaluwarsa' => fn ($a) => $a->tgl_kedaluwarsa && Carbon::parse($a->tgl_kedaluwarsa)->isPast(),
            'Sesuai' => fn ($a) => $a->latestInspeksi && in_array($a->latestInspeksi->status, ['sesuai', 'layak', 'baik']),
            'Tidak Sesuai' => fn ($a) => $a->latestInspeksi && in_array($a->latestInspeksi->status, ['tidak_sesuai', 'perbaikan', 'rusak', 'service', 'tidak_layak']),
            'Perlu Refill' => fn ($a) => $a->latestInspeksi && in_array($a->latestInspeksi->status, ['perlu_refill', 'isi_ulang']),
        ];

        $resumeTable = [];
        foreach ($resumeRows as $rowName => $filterCallback) {
            $rowData = [];
            $rowTotal = 0;
            foreach ($resumeColumns as $jenis => $kapasitasList) {
                foreach ($kapasitasList as $kapasitas) {
                    $count = $apars->filter(function ($a) use ($jenis, $kapasitas, $filterCallback) {
                        $j = $a->jenis->nama ?? 'Unknown';
                        $k = $a->kapasitas->ukuran ?? 'Unknown';

                        return $j === $jenis && $k === $kapasitas && $filterCallback($a);
                    })->count();
                    $rowData[$jenis][$kapasitas] = $count;
                    $rowTotal += $count;
                }
            }
            $resumeTable[] = [
                'name' => $rowName,
                'data' => $rowData,
                'total' => $rowTotal,
            ];
        }

        $pdf = Pdf::loadView('master-data.pdf', compact('apars', 'aparCadangans', 'resumeColumns', 'resumeTable'))->setPaper('a4', 'portrait');

        return $pdf->stream('Data_APAR_'.date('Ymd_His').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = Apar::with(['lokasi.gedung', 'jenis', 'kapasitas', 'latestInspeksi.user', 'pic']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nomor_ruangan', 'like', "%{$search}%")
                    ->orWhereHas('lokasi', function ($q2) use ($search) {
                        $q2->where('nama', 'like', "%{$search}%")
                            ->orWhereHas('gedung', function ($q3) use ($search) {
                                $q3->where('nama', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($request->filled('gedung_id')) {
            $query->whereHas('lokasi', function ($q) use ($request) {
                $q->where('gedung_id', $request->gedung_id);
            });
        }

        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        if ($request->filled('kapasitas_id')) {
            $query->where('kapasitas_id', $request->kapasitas_id);
        }

        if ($request->filled('jenis_id')) {
            $query->where('jenis_id', $request->jenis_id);
        }

        $apars = $query->orderBy('id', 'asc')->get();

        $filename = 'Data_APAR_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'No',
            'Fire Extinguisher Code / Kode APAR',
            'Room No / No. Ruangan',
            'Location / Lokasi',
            'Building / Gedung',
            'Fire Extinguisher Type / Jenis APAR',
            'Fill Capacity / Kapasitas Isi',
            'Fire Class / Kelas Kebakaran',
            'Expired Date / Tanggal Kedaluwarsa',
            'Last Refill / Terakhir Isi Ulang',
            'Last Inspection / Terakhir Inspeksi',
            'Qty / Jml',
            'PIC / Penanggung Jawab',
            'Final Status / Status Akhir',
        ];

        $callback = function () use ($apars, $columns) {
            $file = fopen('php://output', 'w');
            // add BOM to fix UTF-8 in Excel
            fwrite($file, $bom = (chr(0xEF).chr(0xBB).chr(0xBF)));
            fputcsv($file, $columns);

            foreach ($apars as $index => $apar) {
                $jenisNamaL = strtolower($apar->jenis->nama ?? '');
                $kelas = '-';
                if (strpos($jenisNamaL, 'dry chemical') !== false || strpos($jenisNamaL, 'powder') !== false) {
                    $kelas = 'A-B-C';
                } elseif (strpos($jenisNamaL, 'carbon') !== false || strpos($jenisNamaL, 'dioxide') !== false || strpos($jenisNamaL, 'co2') !== false) {
                    $kelas = 'B-C';
                }

                $lastInspeksi = $apar->latestInspeksi;
                $picName = '-';
                if ($lastInspeksi && $lastInspeksi->user) {
                    $picName = $lastInspeksi->user->name;
                } elseif ($apar->pic) {
                    $picName = $apar->pic->name;
                }

                $statusRaw = $lastInspeksi ? strtolower($lastInspeksi->status) : null;
                if ((int) $apar->qty <= 0) {
                    $statusRaw = 'perlu_refill';
                }
                $statusLabel = '-';
                if ($statusRaw === 'sesuai' || $statusRaw === 'layak') {
                    $statusLabel = 'Compliant / Sesuai';
                } elseif ($statusRaw === 'tidak_sesuai' || in_array($statusRaw, ['perbaikan', 'rusak', 'service', 'tidak_layak'])) {
                    $statusLabel = 'Non-Compliant / Tidak Sesuai';
                } elseif ($statusRaw === 'perlu_refill' || $statusRaw === 'isi_ulang') {
                    $statusLabel = 'Needs Refill / Perlu Refill';
                }

                $row = [
                    $index + 1,
                    $apar->kode,
                    $apar->nomor_ruangan ?? '-',
                    $apar->lokasi->nama ?? 'n/a',
                    $apar->lokasi->gedung->nama ?? 'n/a',
                    $apar->jenis->nama ?? 'n/a',
                    $apar->kapasitas->ukuran ?? 'n/a',
                    $kelas,
                    $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->format('d M Y') : '-',
                    $apar->tgl_isi_ulang ? $apar->tgl_isi_ulang->format('d M Y') : '-',
                    $lastInspeksi ? $lastInspeksi->created_at->format('d M Y') : '-',
                    $apar->qty,
                    $picName,
                    $statusLabel,
                ];

                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function previewExcel(Request $request)
    {
        $query = Apar::with(['lokasi.gedung', 'jenis', 'kapasitas', 'latestInspeksi.user', 'pic']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nomor_ruangan', 'like', "%{$search}%")
                    ->orWhereHas('lokasi', function ($q2) use ($search) {
                        $q2->where('nama', 'like', "%{$search}%")
                            ->orWhereHas('gedung', function ($q3) use ($search) {
                                $q3->where('nama', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($request->filled('gedung_id')) {
            $query->whereHas('lokasi', function ($q) use ($request) {
                $q->where('gedung_id', $request->gedung_id);
            });
        }

        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        if ($request->filled('kapasitas_id')) {
            $query->where('kapasitas_id', $request->kapasitas_id);
        }

        if ($request->filled('jenis_id')) {
            $query->where('jenis_id', $request->jenis_id);
        }

        $apars = $query->orderBy('id', 'asc')->get();
        $columns = [
            'No',
            'Fire Extinguisher Code / Kode APAR',
            'Room No / No. Ruangan',
            'Location / Lokasi',
            'Building / Gedung',
            'Fire Extinguisher Type / Jenis APAR',
            'Fill Capacity / Kapasitas Isi',
            'Fire Class / Kelas Kebakaran',
            'Expired Date / Tanggal Kedaluwarsa',
            'Last Refill / Terakhir Isi Ulang',
            'Last Inspection / Terakhir Inspeksi',
            'Qty / Jml',
            'PIC / Penanggung Jawab',
            'Final Status / Status Akhir',
        ];
        $rows = [];

        foreach ($apars as $index => $apar) {
            $jenisNamaL = strtolower($apar->jenis->nama ?? '');
            $kelas = '-';
            if (strpos($jenisNamaL, 'dry chemical') !== false || strpos($jenisNamaL, 'powder') !== false) {
                $kelas = 'A-B-C';
            } elseif (strpos($jenisNamaL, 'carbon') !== false || strpos($jenisNamaL, 'dioxide') !== false || strpos($jenisNamaL, 'co2') !== false) {
                $kelas = 'B-C';
            }

            $lastInspeksi = $apar->latestInspeksi;
            $picName = '-';
            if ($lastInspeksi && $lastInspeksi->user) {
                $picName = $lastInspeksi->user->name;
            } elseif ($apar->pic) {
                $picName = $apar->pic->name;
            }

            $statusRaw = $lastInspeksi ? strtolower($lastInspeksi->status) : null;
            if ((int) $apar->qty <= 0) {
                $statusRaw = 'perlu_refill';
            }
            $statusLabel = '-';
            if ($statusRaw === 'sesuai' || $statusRaw === 'layak') {
                $statusLabel = 'Compliant / Sesuai';
            } elseif ($statusRaw === 'tidak_sesuai' || in_array($statusRaw, ['perbaikan', 'rusak', 'service', 'tidak_layak'])) {
                $statusLabel = 'Non-Compliant / Tidak Sesuai';
            } elseif ($statusRaw === 'perlu_refill' || $statusRaw === 'isi_ulang') {
                $statusLabel = 'Needs Refill / Perlu Refill';
            }

            $rows[] = [
                $index + 1,
                $apar->kode,
                $apar->nomor_ruangan ?? '-',
                $apar->lokasi->nama ?? 'n/a',
                $apar->lokasi->gedung->nama ?? 'n/a',
                $apar->jenis->nama ?? 'n/a',
                $apar->kapasitas->ukuran ?? 'n/a',
                $kelas,
                $apar->tgl_kedaluwarsa ? $apar->tgl_kedaluwarsa->format('d M Y') : '-',
                $apar->tgl_isi_ulang ? $apar->tgl_isi_ulang->format('d M Y') : '-',
                $lastInspeksi ? $lastInspeksi->created_at->format('d M Y') : '-',
                $apar->qty,
                $picName,
                $statusLabel,
            ];
        }

        return response()->json([
            'headers' => $columns,
            'rows' => $rows,
        ]);
    }

    public function storeGedung(Request $request)
    {
        $request->validate(['nama' => 'required|string|max:255|unique:gedung,nama'], ['nama.unique' => 'Nama gedung ini sudah terdaftar.']);
        Gedung::create($request->only('nama'));

        return redirect()->back()->with('success', __('Data gedung berhasil ditambahkan!'));
    }

    public function storeLokasi(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'gedung_id' => 'required|exists:gedung,id',
            'nomor_ruangan' => 'nullable|string|max:100',
            'lantai' => 'nullable|string|max:50',
            'klasifikasi' => 'nullable|string|max:50',
        ]);

        $this->validateUniqueRoomNumber($request);

        $klasifikasi = $request->klasifikasi;
        if (! $klasifikasi && $request->klasifikasi_1) {
            $klasifikasi = trim($request->klasifikasi_1);
            if ($request->klasifikasi_2 && trim($request->klasifikasi_2) !== '' && trim($request->klasifikasi_2) !== '-') {
                $klasifikasi .= '/'.trim($request->klasifikasi_2);
            }
        }

        Lokasi::create([
            'nama' => $request->nama,
            'gedung_id' => $request->gedung_id,
            'nomor_ruangan' => $request->nomor_ruangan,
            'lantai' => $request->lantai,
            'klasifikasi' => $klasifikasi,
        ]);

        return redirect()->back()->with('success', __('Data lokasi berhasil ditambahkan!'));
    }

    public function storeJenis(Request $request)
    {
        $request->validate(['nama' => 'required|string|max:255|unique:jenis_apar,nama'], ['nama.unique' => 'Jenis APAR ini sudah terdaftar.']);
        JenisApar::create($request->only('nama'));

        return redirect()->back()->with('success', __('Jenis APAR berhasil ditambahkan!'));
    }

    public function storeKapasitas(Request $request)
    {
        if ($request->has('ukuran')) {
            $ukuran = trim($request->ukuran);
            if (! preg_match('/(?i)kg$/', $ukuran)) {
                $request->merge(['ukuran' => trim($ukuran).' Kg']);
            }
        }

        $request->validate(['ukuran' => 'required|string|max:255|unique:kapasitas_apar,ukuran'], ['ukuran.unique' => 'Kapasitas APAR ini sudah terdaftar.']);
        KapasitasApar::create($request->only('ukuran'));

        return redirect()->back()->with('success', __('Kapasitas APAR berhasil ditambahkan!'));
    }

    public function destroyGedung(Gedung $gedung)
    {
        $gedung->delete();

        return redirect()->back()->with('success', __('Data gedung berhasil dihapus!'));
    }

    public function destroyLokasi(Lokasi $lokasi)
    {
        $lokasi->delete();

        return redirect()->back()->with('success', __('Data lokasi berhasil dihapus!'));
    }

    public function destroyJenis(JenisApar $jenisApar)
    {
        $jenisApar->delete();

        return redirect()->back()->with('success', __('Jenis APAR berhasil dihapus!'));
    }

    public function destroyKapasitas(KapasitasApar $kapasitasApar)
    {
        $kapasitasApar->delete();

        return redirect()->back()->with('success', __('Kapasitas APAR berhasil dihapus!'));
    }

    public function getQrData(Apar $apar)
    {
        $url = route('scan.apar', $apar->kode);
        $svg = QrCode::size(250)->generate($url);

        return response()->json([
            'id' => $apar->id,
            'kode' => $apar->kode,
            'svg' => (string) $svg,
        ]);
    }

    public function downloadQr(Apar $apar)
    {
        $url = route('scan.apar', $apar->kode);
        $svg = QrCode::size(500)->generate($url);

        // Return as downloadable SVG file
        return response((string) $svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="QR_'.$apar->kode.'.svg"');
    }

    public function printAllQr()
    {
        $apars = Apar::with(['lokasi.gedung', 'jenis', 'kapasitas'])->get()->sortBy('kode', SORT_NATURAL);

        return view('master-data.print-all', compact('apars'));
    }

    public function printSingleQr(Apar $apar)
    {
        $apar->load(['lokasi.gedung', 'jenis', 'kapasitas']);

        return view('master-data.print-single', compact('apar'));
    }

    public function destroyApar(Apar $apar)
    {
        $apar->delete();

        return redirect()->back()->with('success', __('Data APAR berhasil dihapus!'));
    }

    public function storeApar(Request $request)
    {
        $request->validate([
            'gedung_id' => 'required|exists:gedung,id',
            'lokasi' => 'required|string|max:255',
            'nomor_ruangan' => 'nullable|string|max:100',
            'jenis_id' => 'required|exists:jenis_apar,id',
            'kapasitas_id' => 'required|exists:kapasitas_apar,id',
            'vendor' => 'nullable|string|max:255',
            'tgl_kedaluwarsa' => 'nullable|date',
            'tgl_isi_ulang' => 'nullable|date',
            'nomor_apar' => ['required', 'string', 'max:10', 'regex:/^[1-9][0-9]*$/'],
        ], [
            'nomor_apar.regex' => 'Nomor APAR harus berupa angka dan dimulai dari 1-9 (tidak boleh diawali angka 0).',
        ]);

        $lokasi = Lokasi::firstOrCreate([
            'gedung_id' => $request->gedung_id,
            'nama' => $request->lokasi,
        ]);

        if ($request->filled('gunakan_cadangan') && $request->filled('cadangan_id')) {
            $cadangan = AparCadangan::find($request->cadangan_id);
            if ($cadangan) {
                $qty = $request->input('qty', 1);
                if ($cadangan->total < $qty) {
                    return redirect()->back()->withErrors(['cadangan_id' => 'Stok cadangan tidak mencukupi (Sisa: '.$cadangan->total.').'])->withInput()->with('form_type', 'tambah_apar');
                }
                $cadangan->decrement('total', $qty);
                // Force the request to use the cadangan's specs
                $request->merge([
                    'jenis_id' => $cadangan->jenis_id,
                    'kapasitas_id' => $cadangan->kapasitas_id,
                ]);
            }
        }

        // Cek unik nomor APAR terhadap seluruh APAR yang ada
        $inputNumber = (int) $request->nomor_apar;
        $duplicateApar = Apar::all()->first(function ($item) use ($inputNumber) {
            preg_match('/\d+$/', $item->kode ?? '', $matches);

            if (! isset($matches[0])) {
                return false;
            }

            return (int) $matches[0] === $inputNumber;
        });

        if ($duplicateApar) {
            return redirect()->back()->withErrors([
                'nomor_apar' => __('Nomor PFE [:number] sudah digunakan oleh :code! Nomor PFE adalah Primary Key (unik sistem), silakan gunakan nomor lain.', ['number' => $request->nomor_apar, 'code' => $duplicateApar->kode]),
            ])->withInput()->with('form_type', 'tambah_apar');
        }

        $prefix = $this->generatePrefix($lokasi->id, $request->jenis_id);
        $kode = $prefix.$request->nomor_apar;

        // Cek unik kode lengkap juga sebagai fallback
        if (Apar::where('kode', $kode)->exists()) {
            return redirect()->back()->withErrors(['nomor_apar' => __('Nomor APAR ini sudah digunakan untuk prefix :prefix', ['prefix' => $prefix])])->withInput()->with('form_type', 'tambah_apar');
        }

        $data = $request->except(['kode', 'nomor_apar', 'form_type', 'gedung_id', 'lokasi']);
        $data['kode'] = $kode;
        $data['lokasi_id'] = $lokasi->id;
        $data['pic_id'] = auth()->id();
        $data['nomor_ruangan'] = $request->filled('nomor_ruangan') ? $request->nomor_ruangan : ($lokasi->nomor_ruangan ?? null);

        if (! $lokasi->nomor_ruangan && $request->filled('nomor_ruangan')) {
            $lokasi->update(['nomor_ruangan' => $request->nomor_ruangan]);
        }

        if (empty($data['vendor'])) {
            $data['vendor'] = 'N/A';
        }

        Apar::create($data);

        return redirect()->back()->with('success', __('Data APAR berhasil ditambahkan!'));
    }

    public function updateGedung(Request $request, Gedung $gedung)
    {
        $request->validate(['nama' => 'required|string|max:255|unique:gedung,nama,'.$gedung->id], ['nama.unique' => 'Nama gedung ini sudah terdaftar.']);
        $gedung->update($request->only('nama'));

        return redirect()->back()->with('success', __('Data gedung berhasil diperbarui!'));
    }

    public function updateLokasi(Request $request, Lokasi $lokasi)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'gedung_id' => 'required|exists:gedung,id',
            'nomor_ruangan' => 'nullable|string|max:100',
            'lantai' => 'nullable|string|max:50',
            'klasifikasi' => 'nullable|string|max:50',
        ]);

        $this->validateUniqueRoomNumber($request, $lokasi->id);

        $klasifikasi = $request->klasifikasi;
        if (! $klasifikasi && $request->klasifikasi_1) {
            $klasifikasi = trim($request->klasifikasi_1);
            if ($request->klasifikasi_2 && trim($request->klasifikasi_2) !== '' && trim($request->klasifikasi_2) !== '-') {
                $klasifikasi .= '/'.trim($request->klasifikasi_2);
            }
        }

        $lokasi->update([
            'nama' => $request->nama,
            'gedung_id' => $request->gedung_id,
            'nomor_ruangan' => $request->nomor_ruangan,
            'lantai' => $request->lantai,
            'klasifikasi' => $klasifikasi,
        ]);

        return redirect()->back()->with('success', __('Data lokasi berhasil diperbarui!'));
    }

    /**
     * Memastikan nomor ruangan dan nomor urut ruangan tidak duplikat dalam 1 gedung.
     */
    private function validateUniqueRoomNumber(Request $request, ?int $ignoreLokasiId = null): void
    {
        $nomorRuangan = trim((string) $request->nomor_ruangan);
        $gedungId = $request->gedung_id;

        if ($nomorRuangan === '' || ! $gedungId) {
            return;
        }

        $targetSeq = null;
        if (str_contains($nomorRuangan, '.')) {
            $parts = explode('.', $nomorRuangan);
            $last = trim(end($parts));
            if (preg_match('/^0*(\d+)$/', $last, $m)) {
                $targetSeq = $m[1];
            } else {
                $targetSeq = strtolower($last);
            }
        } elseif (preg_match('/^0*(\d+)$/', $nomorRuangan, $m)) {
            $targetSeq = $m[1];
        }

        $existingRooms = Lokasi::where('gedung_id', $gedungId)
            ->when($ignoreLokasiId, fn ($q) => $q->where('id', '!=', $ignoreLokasiId))
            ->whereNotNull('nomor_ruangan')
            ->get(['id', 'nomor_ruangan']);

        foreach ($existingRooms as $existing) {
            $existCode = trim((string) $existing->nomor_ruangan);
            if ($existCode === '') {
                continue;
            }

            if (strcasecmp($existCode, $nomorRuangan) === 0) {
                throw ValidationException::withMessages([
                    'nomor_ruangan' => __('Nomor ruangan (:code) sudah terdaftar di gedung yang dipilih.', ['code' => $nomorRuangan]),
                ]);
            }

            if ($targetSeq !== null) {
                $existSeq = null;
                if (str_contains($existCode, '.')) {
                    $parts = explode('.', $existCode);
                    $existLast = trim(end($parts));
                    if (preg_match('/^0*(\d+)$/', $existLast, $m)) {
                        $existSeq = $m[1];
                    } else {
                        $existSeq = strtolower($existLast);
                    }
                } elseif (preg_match('/^0*(\d+)$/', $existCode, $m)) {
                    $existSeq = $m[1];
                }

                if ($existSeq !== null && $existSeq === $targetSeq) {
                    $displaySeq = $last ?? $targetSeq;
                    throw ValidationException::withMessages([
                        'nomor_ruangan' => __('Nomor urut ruangan (:seq) sudah digunakan di gedung ini.', ['seq' => $displaySeq]),
                    ]);
                }
            }
        }
    }

    public function updateJenis(Request $request, JenisApar $jenisApar)
    {
        $request->validate(['nama' => 'required|string|max:255|unique:jenis_apar,nama,'.$jenisApar->id], ['nama.unique' => 'Jenis APAR ini sudah terdaftar.']);
        $jenisApar->update($request->only('nama'));

        return redirect()->back()->with('success', __('Jenis APAR berhasil diperbarui!'));
    }

    public function updateKapasitas(Request $request, KapasitasApar $kapasitasApar)
    {
        if ($request->has('ukuran')) {
            $ukuran = trim($request->ukuran);
            if (! preg_match('/(?i)kg$/', $ukuran)) {
                $request->merge(['ukuran' => trim($ukuran).' Kg']);
            }
        }

        $request->validate(['ukuran' => 'required|string|max:255|unique:kapasitas_apar,ukuran,'.$kapasitasApar->id], ['ukuran.unique' => 'Kapasitas APAR ini sudah terdaftar.']);
        $kapasitasApar->update($request->only('ukuran'));

        return redirect()->back()->with('success', __('Kapasitas APAR berhasil diperbarui!'));
    }

    public function updateApar(Request $request, Apar $apar)
    {
        $request->validate([
            'gedung_id' => 'required|exists:gedung,id',
            'lokasi' => 'required|string|max:255',
            'nomor_ruangan' => 'nullable|string|max:100',
            'jenis_id' => 'required|exists:jenis_apar,id',
            'kapasitas_id' => 'required|exists:kapasitas_apar,id',
            'vendor' => 'nullable|string|max:255',
            'tgl_kedaluwarsa' => 'nullable|date',
            'tgl_isi_ulang' => 'nullable|date',
            'nomor_apar' => ['required', 'string', 'max:10', 'regex:/^[1-9][0-9]*$/'],
        ], [
            'nomor_apar.regex' => 'Nomor APAR harus berupa angka dan dimulai dari 1-9 (tidak boleh diawali angka 0).',
        ]);

        $lokasi = Lokasi::firstOrCreate([
            'gedung_id' => $request->gedung_id,
            'nama' => $request->lokasi,
        ]);

        if ($request->filled('gunakan_cadangan') && $request->filled('cadangan_id')) {
            $cadangan = AparCadangan::find($request->cadangan_id);
            if ($cadangan) {
                $qty = $request->input('qty', 1);
                // Check if qty is more than available stock
                if ($cadangan->total < $qty) {
                    return redirect()->back()->withErrors(['cadangan_id' => 'Stok cadangan tidak mencukupi (Sisa: '.$cadangan->total.').'])->withInput()->with('form_type', 'edit_apar');
                }
                $cadangan->decrement('total', $qty);
                $request->merge([
                    'jenis_id' => $cadangan->jenis_id,
                    'kapasitas_id' => $cadangan->kapasitas_id,
                ]);
            }
        }

        $data = $request->except(['kode', 'nomor_apar', 'form_type', 'gedung_id', 'lokasi']);
        $data['lokasi_id'] = $lokasi->id;
        $data['pic_id'] = auth()->id();
        $data['nomor_ruangan'] = $request->filled('nomor_ruangan') ? $request->nomor_ruangan : ($lokasi->nomor_ruangan ?? null);

        if (! $lokasi->nomor_ruangan && $request->filled('nomor_ruangan')) {
            $lokasi->update(['nomor_ruangan' => $request->nomor_ruangan]);
        }

        if (empty($data['vendor'])) {
            $data['vendor'] = 'N/A';
        }

        // Cek unik nomor APAR terhadap seluruh APAR lain
        $inputNumber = (int) $request->nomor_apar;
        $duplicateApar = Apar::where('id', '!=', $apar->id)->get()->first(function ($item) use ($inputNumber) {
            preg_match('/\d+$/', $item->kode ?? '', $matches);

            if (! isset($matches[0])) {
                return false;
            }

            return (int) $matches[0] === $inputNumber;
        });

        if ($duplicateApar) {
            return redirect()->back()->withErrors([
                'nomor_apar' => __('Nomor PFE [:number] sudah digunakan oleh :code! Nomor PFE adalah Primary Key (unik sistem), silakan gunakan nomor lain.', ['number' => $request->nomor_apar, 'code' => $duplicateApar->kode]),
            ])->withInput()->with('form_type', 'edit_apar')->with('id', $apar->id);
        }

        $prefix = $this->generatePrefix($lokasi->id, $request->jenis_id);
        $newKode = $prefix.$request->nomor_apar;

        // Cek unik untuk kode yang baru jika ada perubahan
        if ($newKode !== $apar->kode && Apar::where('kode', $newKode)->where('id', '!=', $apar->id)->exists()) {
            return redirect()->back()->withErrors(['nomor_apar' => __('Nomor APAR ini sudah digunakan untuk prefix :prefix', ['prefix' => $prefix])])->withInput()->with('form_type', 'edit_apar')->with('id', $apar->id);
        }

        $data['kode'] = $newKode;

        $apar->update($data);

        return redirect()->back()->with('success', __('Data APAR berhasil diperbarui!'));
    }

    private function generatePrefix($lokasi_id, $jenis_id)
    {
        $lokasi = Lokasi::with('gedung')->find($lokasi_id);
        $jenis = JenisApar::find($jenis_id);

        $gedungNama = $lokasi->gedung->nama ?? '';
        $gedungCode = '';
        if (strpos(strtoupper($gedungNama), 'BU-') !== false) {
            $parts = explode('BU-', strtoupper($gedungNama));
            $gedungCode = trim($parts[1] ?? '');
        }
        if (empty($gedungCode)) {
            $gedungCode = substr(strtoupper($gedungNama), 0, 1);
        }

        $jenisCode = 'C';
        $jenisNamaL = strtolower($jenis->nama ?? '');
        if (strpos($jenisNamaL, 'dry chemical') !== false || strpos($jenisNamaL, 'powder') !== false) {
            $jenisCode = 'A';
        } elseif (strpos($jenisNamaL, 'carbon') !== false || strpos($jenisNamaL, 'dioxide') !== false || strpos($jenisNamaL, 'co2') !== false) {
            $jenisCode = 'B';
        } else {
            $jenisCode = substr(strtoupper($jenis->nama ?? 'C'), 0, 1);
        }

        $buildingPrefix = "PFE-{$gedungCode}-";

        return $buildingPrefix.$jenisCode;
    }

    public function history(Apar $apar)
    {
        // Hanya ambil 1 data aktivitas terakhir (yang paling baru)
        $activities = $apar->activities()->with('causer')->latest()->take(1)->get();

        return view('master-data.history-partial', compact('apar', 'activities'));
    }

    public function storeAparCadangan(Request $request)
    {
        if (auth()->user() && auth()->user()->role === 'Staff') {
            abort(403, __('Role Staff tidak memiliki izin untuk mengelola stok cadangan APAR.'));
        }

        $request->validate([
            'gedung_id' => 'required|exists:gedung,id',
            'jenis_id' => 'required|exists:jenis_apar,id',
            'kapasitas_id' => 'required|exists:kapasitas_apar,id',
            'total' => 'required|integer|min:0',
        ]);

        $exists = AparCadangan::where('gedung_id', $request->gedung_id)
            ->where('jenis_id', $request->jenis_id)
            ->where('kapasitas_id', $request->kapasitas_id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['gedung_id' => __('Data sudah ada, silakan lakukan update data.')])
                ->withInput()
                ->with('error', __('Data sudah ada, silakan lakukan update data.'));
        }

        Log::info('STORE: ', $request->all());
        AparCadangan::create($request->all());

        return redirect()->route('master-data.index')->with('success', __('Data APAR Cadangan berhasil ditambahkan.'));
    }

    public function updateAparCadangan(Request $request, AparCadangan $aparCadangan)
    {
        if (auth()->user() && auth()->user()->role === 'Staff') {
            abort(403, __('Role Staff tidak memiliki izin untuk mengelola stok cadangan APAR.'));
        }

        $request->validate([
            'gedung_id' => 'required|exists:gedung,id',
            'jenis_id' => 'required|exists:jenis_apar,id',
            'kapasitas_id' => 'required|exists:kapasitas_apar,id',
            'total' => 'required|integer|min:0',
        ]);

        $exists = AparCadangan::where('gedung_id', $request->gedung_id)
            ->where('jenis_id', $request->jenis_id)
            ->where('kapasitas_id', $request->kapasitas_id)
            ->where('id', '!=', $aparCadangan->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['gedung_id' => __('Data sudah ada, silakan lakukan update data.')])
                ->withInput()
                ->with('error', __('Data sudah ada, silakan lakukan update data.'));
        }

        Log::info('UPDATE: ', $request->all());
        $aparCadangan->update($request->all());

        return redirect()->route('master-data.index')->with('success', __('Data APAR Cadangan berhasil diperbarui.'));
    }

    public function destroyAparCadangan(AparCadangan $aparCadangan)
    {
        if (auth()->user() && auth()->user()->role === 'Staff') {
            abort(403, __('Role Staff tidak memiliki izin untuk mengelola stok cadangan APAR.'));
        }

        $aparCadangan->delete();

        return redirect()->route('master-data.index')->with('success', __('Data APAR Cadangan berhasil dihapus.'));
    }
}
