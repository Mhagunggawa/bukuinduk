<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterMataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'master_mata_pelajaran';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'kelompok',
        'sub_kelompok',
        'no_urut',
    ];

    public function nilaiAkademik(): HasMany
    {
        return $this->hasMany(NilaiAkademik::class, 'mata_pelajaran_id');
    }
}
