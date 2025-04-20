<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KaryawanShift extends Model
{
    //
    protected $fillable = [
        'karyawan_id',
        'shift_id',
        'tanggal_mulai',
        'tanggal_selesai'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
