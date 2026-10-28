<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Lokasi extends Model
{
    use LogsActivity;

    protected $table = 'lokasi';

    protected $fillable = ['gedung_id', 'nama', 'nomor_ruangan', 'lantai', 'klasifikasi'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                $nama = $this->nama ?? 'Lokasi';
                $gedung = $this->gedung?->nama ?? '-';

                return match ($eventName) {
                    'created' => "Menambahkan data lokasi baru [{$nama}] di {$gedung}",
                    'updated' => "Memperbarui data lokasi [{$nama}] di {$gedung}",
                    'deleted' => "Menghapus data lokasi [{$nama}] di {$gedung}",
                    default => "Lokasi [{$nama}] telah di-{$eventName}",
                };
            });
    }

    public function gedung()
    {
        return $this->belongsTo(Gedung::class);
    }

    public function apar()
    {
        return $this->hasMany(Apar::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
