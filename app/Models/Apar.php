<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Apar extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'apar';

    protected $fillable = ['kode', 'nomor_ruangan', 'qty', 'lokasi_id', 'jenis_id', 'kapasitas_id', 'vendor', 'tgl_kedaluwarsa', 'tgl_isi_ulang', 'foto', 'pic_id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "APAR telah di-{$eventName}");
    }

    protected $casts = [
        'tgl_kedaluwarsa' => 'date',
        'tgl_isi_ulang' => 'date',
    ];

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function jenis()
    {
        return $this->belongsTo(JenisApar::class, 'jenis_id');
    }

    public function kapasitas()
    {
        return $this->belongsTo(KapasitasApar::class, 'kapasitas_id');
    }

    public function inspeksis()
    {
        return $this->hasMany(Inspeksi::class);
    }

    public function latestInspeksi()
    {
        return $this->hasOne(Inspeksi::class)->latestOfMany();
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }
}
