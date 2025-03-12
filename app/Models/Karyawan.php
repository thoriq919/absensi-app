<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    //
    protected $fillable = [
        'user_id',
        'nama',
        'alamat',
        'no_telp',
        'tanggal_masuk',
        'status_karyawan',
        'rfid_number',
        'saldo_cuti',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
