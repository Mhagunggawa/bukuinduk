<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterBidangStudi extends Model
{
    use HasFactory;

    protected $table = 'master_bidang_studi';

    protected $fillable = [
        'kode',
        'nama_bidang_studi',
    ];

    public function programKeahlian(): HasMany
    {
        return $this->hasMany(MasterProgramKeahlian::class, 'bidang_studi_id');
    }
}
