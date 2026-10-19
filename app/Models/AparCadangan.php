<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AparCadangan extends Model
{
    protected $table = 'apar_cadangans';

    protected $fillable = ['gedung_id', 'jenis_id', 'kapasitas_id', 'total'];

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
