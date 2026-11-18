<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterProgramKeahlian extends Model
{
    use HasFactory;

    protected $table = 'master_program_keahlian';

    protected $fillable = [
        'bidang_studi_id',
        'kode',
        'nama_program_keahlian',
    ];

    public function bidangStudi(): BelongsTo
    {
        return $this->belongsTo(MasterBidangStudi::class, 'bidang_studi_id');
    }

    public function konsentrasiKeahlian(): HasMany
    {
        return $this->hasMany(MasterKonsentrasiKeahlian::class, 'program_keahlian_id');
    }
}
