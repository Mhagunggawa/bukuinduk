<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterTahunPelajaran extends Model
{
    use HasFactory;

    protected $table = 'master_tahun_pelajaran';

    protected $fillable = [
        'tahun_pelajaran',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
        ];
    }

    public function nilaiAkademik(): HasMany
    {
        return $this->hasMany(NilaiAkademik::class, 'tahun_pelajaran_id');
    }

    public function rekapSemester(): HasMany
    {
        return $this->hasMany(RekapSemesterSiswa::class, 'tahun_pelajaran_id');
    }
}
