<?php

namespace App\Models;

use App\Observers\CutiObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([CutiObserver::class])]
class Cuti extends Model
{
    //
    protected $fillable = [
        'karyawan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'status_pengajuan',
        'dokumen_pendukung'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }    
}
