<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterEkstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'master_ekstrakurikuler';

    protected $fillable = [
        'kode_ekskul',
        'nama_ekskul',
        'no_urut',
    ];

    public function nilaiEkstrakurikuler(): HasMany
    {
        return $this->hasMany(NilaiEkstrakurikuler::class, 'ekstrakurikuler_id');
    }
}
