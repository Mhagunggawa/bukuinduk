<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiAkademik extends Model
{
    use HasFactory;

    protected $table = 'nilai_akademik';

    protected $fillable = [
        'siswa_id',
        'mata_pelajaran_id',
        'tahun_pelajaran_id',
        'semester_ke',
        'nilai_angka',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'semester_ke' => 'integer',
            'nilai_angka' => 'float',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MasterMataPelajaran::class, 'mata_pelajaran_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(MasterTahunPelajaran::class, 'tahun_pelajaran_id');
    }
}
