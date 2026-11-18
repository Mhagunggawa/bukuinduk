<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiEkstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'nilai_ekstrakurikuler';

    protected $fillable = [
        'siswa_id',
        'ekstrakurikuler_id',
        'semester_ke',
        'nilai_predikat',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'semester_ke' => 'integer',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function ekstrakurikuler(): BelongsTo
    {
        return $this->belongsTo(MasterEkstrakurikuler::class, 'ekstrakurikuler_id');
    }
}
