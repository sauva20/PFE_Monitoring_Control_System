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
use Illuminate\Validation\Rule;
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
            'APAR Expired' => fn ($a) => $a->tgl_kedaluwarsa && Carbon::parse($a->tgl_kedaluwarsa)->isPast(),
            'Sesuai' => fn ($a) => $a->latestInspeksi && in_array($a->latestInspeksi->status, ['layak', 'baik']),
            'Perlu Perbaikan' => fn ($a) => $a->latestInspeksi && $a->latestInspeksi->status === 'perbaikan',
            'Perlu Isi Ulang' => fn ($a) => $a->latestInspeksi && $a->latestInspeksi->status === 'isi_ulang',
            'Rusak / Servis' => fn ($a) => $a->latestInspeksi && in_array($a->latestInspeksi->status, ['rusak', 'service']),
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

        return view('master-data.index', compact('gedungs', 'lokasis', 'jenisApars', 'kapasitasApars', 'apars', 'aparCadangans', 'resumeColumns', 'resumeTable'));
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
            'APAR Expired' => fn ($a) => $a->tgl_kedaluwarsa && Carbon::parse($a->tgl_kedaluwarsa)->isPast(),
            'Sesuai' => fn ($a) => $a->latestInspeksi && in_array($a->latestInspeksi->status, ['layak', 'baik']),
            'Perlu Perbaikan' => fn ($a) => $a->latestInspeksi && $a->latestInspeksi->status === 'perbaikan',
            'Perlu Isi Ulang' => fn ($a) => $a->latestInspeksi && $a->latestInspeksi->status === 'isi_ulang',
            'Rusak / Servis' => fn ($a) => $a->latestInspeksi && in_array($a->latestInspeksi->status, ['rusak', 'service']),
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

        $columns = [__('No'), __('PFE ID'), __('Room No'), __('Location'), __('Building'), __('Type'), __('Capacity'), __('Fire Class'), __('Expiry Date'), __('Last Refill'), __('Qty'), __('PIC'), __('Last Inspection')];

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
                    $apar->qty,
                    $picName,
                    $lastInspeksi ? $lastInspeksi->created_at->format('d M Y') : '-',
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
        $columns = [__('No'), __('PFE ID'), __('Room No'), __('Location'), __('Building'), __('Type'), __('Capacity'), __('Fire Class'), __('Expiry Date'), __('Last Refill'), __('Qty'), __('PIC'), __('Last Inspection')];
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
                $apar->qty,
                $picName,
                $lastInspeksi ? $lastInspeksi->created_at->format('d M Y') : '-',
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
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lokasi')->where(fn ($query) => $query->where('gedung_id', $request->gedung_id)),
            ],
            'gedung_id' => 'required|exists:gedung,id',
        ], [
            'nama.unique' => 'Lokasi dengan nama ini sudah ada di gedung yang dipilih.',
        ]);
        Lokasi::create($request->only('nama', 'gedung_id'));

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
            'nomor_apar' => 'required|string|max:10',
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

        $prefix = $this->generatePrefix($lokasi->id, $request->jenis_id);
        $kode = $prefix.$request->nomor_apar;

        // Cek unik
        if (Apar::where('kode', $kode)->exists()) {
            return redirect()->back()->withErrors(['nomor_apar' => 'Nomor APAR ini sudah digunakan untuk prefix '.$prefix])->withInput()->with('form_type', 'tambah_apar');
        }

        $data = $request->except(['kode', 'nomor_apar', 'form_type', 'gedung_id', 'lokasi']);
        $data['kode'] = $kode;
        $data['lokasi_id'] = $lokasi->id;
        $data['pic_id'] = auth()->id();

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
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lokasi')->where(fn ($query) => $query->where('gedung_id', $request->gedung_id)),
            ],
            'gedung_id' => 'required|exists:gedung,id',
        ], [
            'nama.unique' => 'Lokasi dengan nama ini sudah ada di gedung yang dipilih.',
        ]);
        $lokasi->update($request->only('nama', 'gedung_id'));

        return redirect()->back()->with('success', __('Data lokasi berhasil diperbarui!'));
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
            'nomor_apar' => 'required|string|max:10',
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

        if (empty($data['vendor'])) {
            $data['vendor'] = 'N/A';
        }

        $prefix = $this->generatePrefix($lokasi->id, $request->jenis_id);
        $newKode = $prefix.$request->nomor_apar;

        // Cek unik untuk kode yang baru jika ada perubahan
        if ($newKode !== $apar->kode && Apar::where('kode', $newKode)->where('id', '!=', $apar->id)->exists()) {
            return redirect()->back()->withErrors(['nomor_apar' => 'Nomor APAR ini sudah digunakan untuk prefix '.$prefix])->withInput()->with('form_type', 'edit_apar')->with('id', $apar->id);
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
        $request->validate([
            'gedung_id' => 'required|exists:gedung,id',
            'jenis_id' => 'required|exists:jenis_apar,id',
            'kapasitas_id' => 'required|exists:kapasitas_apar,id',
            'total' => 'required|integer|min:0',
        ]);

        Log::info('STORE: ', $request->all());
        AparCadangan::create($request->all());

        return redirect()->route('master-data.index')->with('success', __('Data APAR Cadangan berhasil ditambahkan.'));
    }

    public function updateAparCadangan(Request $request, AparCadangan $aparCadangan)
    {
        $request->validate([
            'gedung_id' => 'required|exists:gedung,id',
            'jenis_id' => 'required|exists:jenis_apar,id',
            'kapasitas_id' => 'required|exists:kapasitas_apar,id',
            'total' => 'required|integer|min:0',
        ]);

        Log::info('UPDATE: ', $request->all());
        $aparCadangan->update($request->all());

        return redirect()->route('master-data.index')->with('success', __('Data APAR Cadangan berhasil diperbarui.'));
    }

    public function destroyAparCadangan(AparCadangan $aparCadangan)
    {
        $aparCadangan->delete();

        return redirect()->route('master-data.index')->with('success', __('Data APAR Cadangan berhasil dihapus.'));
    }
}
