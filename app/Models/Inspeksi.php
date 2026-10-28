<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Inspeksi extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'apar_id',
        'user_id',
        'checklist',
        'status',
        'catatan_tambahan',
    ];

    protected $casts = [
        'checklist' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                $kode = $this->apar?->kode ?? 'APAR';
                $status = ucfirst(str_replace('_', ' ', $this->status ?? ''));

                return match ($eventName) {
                    'created' => "Melakukan inspeksi rutin APAR [{$kode}] - Status: {$status}",
                    'updated' => "Memperbarui hasil inspeksi APAR [{$kode}] - Status: {$status}",
                    'deleted' => "Menghapus riwayat inspeksi APAR [{$kode}]",
                    default => "Inspeksi APAR [{$kode}] telah di-{$eventName}",
                };
            });
    }

    public function apar()
    {
        return $this->belongsTo(Apar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
