<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKonsentrasiKeahlian extends Model
{
    use HasFactory;

    protected $table = 'master_konsentrasi_keahlian';

    protected $fillable = [
        'program_keahlian_id',
        'kode_singkat',
        'nama_konsentrasi',
    ];

    public function programKeahlian(): BelongsTo
    {
        return $this->belongsTo(MasterProgramKeahlian::class, 'program_keahlian_id');
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'konsentrasi_keahlian_id');
    }
}
