<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gedung extends Model
{
    protected $table = 'gedung';

    protected $fillable = ['nama'];

    public function lokasi()
    {
        return $this->hasMany(Lokasi::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
