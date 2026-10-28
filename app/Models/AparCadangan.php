<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AparCadangan extends Model
{
    use LogsActivity;

    protected $table = 'apar_cadangans';

    protected $fillable = ['gedung_id', 'jenis_id', 'kapasitas_id', 'total'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                $jenis = $this->jenis?->nama ?? 'APAR';
                $gedung = $this->gedung?->nama ?? '-';

                return match ($eventName) {
                    'created' => "Menambahkan stok cadangan APAR {$jenis} di {$gedung} (Jumlah: {$this->total})",
                    'updated' => "Memperbarui stok cadangan APAR {$jenis} di {$gedung} (Jumlah: {$this->total})",
                    'deleted' => "Menghapus stok cadangan APAR {$jenis} di {$gedung}",
                    default => "Stok cadangan APAR {$jenis} telah di-{$eventName}",
                };
            });
    }

    public function gedung()
    {
        return $this->belongsTo(Gedung::class);
    }

    public function jenis()
    {
        return $this->belongsTo(JenisApar::class, 'jenis_id');
    }

    public function kapasitas()
    {
        return $this->belongsTo(KapasitasApar::class, 'kapasitas_id');
    }
}
