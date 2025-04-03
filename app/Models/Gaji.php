<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    //
    protected $fillable = [
        'karyawan_id',
        'tanggal_gaji',
        'gaji_pokok',
        'tunjangan_kehadiran',
        'lembur',
        'potongan',
        'gaji_bersih',
        'validated'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
