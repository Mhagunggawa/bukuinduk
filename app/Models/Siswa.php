<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'konsentrasi_keahlian_id',
        'nis',
        'nisn',
        'nama_lengkap',
        'jenis_kelamin',
        'kelas_saat_ini',
        'tempat_lahir',
        'tanggal_lahir',
        'warga_negara',
        'agama',
        'alamat_siswa',
        'nama_orang_tua',
        'pekerjaan_orang_tua',
        'alamat_orang_tua',
        'diterima_tanggal',
        'diterima_asal_sekolah',
        'diterima_nomor_sttb',
        'diterima_tanggal_sttb',
        'keluar_tanggal',
        'keluar_alasan',
        'tamat_nomor_sttb',
        'tamat_tanggal_sttb',
        'keterangan_lain_lain',
        'pasfoto_path',
        'status_siswa',
    ];

    protected $appends = ['pasfoto_url'];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date:Y-m-d',
            'diterima_tanggal' => 'date:Y-m-d',
            'diterima_tanggal_sttb' => 'date:Y-m-d',
            'keluar_tanggal' => 'date:Y-m-d',
            'tamat_tanggal_sttb' => 'date:Y-m-d',
        ];
    }

    protected function pasfotoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->pasfoto_path) {
                    return null;
                }
                if (str_starts_with($this->pasfoto_path, 'http://') || str_starts_with($this->pasfoto_path, 'https://')) {
                    return $this->pasfoto_path;
                }

                return Storage::url($this->pasfoto_path);
            }
        );
    }

    public function konsentrasiKeahlian(): BelongsTo
    {
        return $this->belongsTo(MasterKonsentrasiKeahlian::class, 'konsentrasi_keahlian_id');
    }

    public function nilaiAkademik(): HasMany
    {
        return $this->hasMany(NilaiAkademik::class, 'siswa_id');
    }

    public function nilaiEkstrakurikuler(): HasMany
    {
        return $this->hasMany(NilaiEkstrakurikuler::class, 'siswa_id');
    }

    public function rekapSemester(): HasMany
    {
        return $this->hasMany(RekapSemesterSiswa::class, 'siswa_id');
    }
}
