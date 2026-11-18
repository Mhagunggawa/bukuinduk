<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapSemesterSiswa extends Model
{
    use HasFactory;

    protected $table = 'rekap_semester_siswa';

    protected $fillable = [
        'siswa_id',
        'tahun_pelajaran_id',
        'semester_ke',
        'nilai_pkl',
        'keterangan_pkl',
        'jumlah_nilai',
        'nilai_rata_rata',
        'ranking',
        'sakit_hari',
        'izin_hari',
        'tanpa_kabar_hari',
        'jumlah_absen_hari',
        'status_kenaikan',
        'tingkat_tujuan',
    ];

    protected function casts(): array
    {
        return [
            'semester_ke' => 'integer',
            'jumlah_nilai' => 'float',
            'nilai_rata_rata' => 'float',
            'ranking' => 'integer',
            'sakit_hari' => 'integer',
            'izin_hari' => 'integer',
            'tanpa_kabar_hari' => 'integer',
            'jumlah_absen_hari' => 'integer',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(MasterTahunPelajaran::class, 'tahun_pelajaran_id');
    }
}
