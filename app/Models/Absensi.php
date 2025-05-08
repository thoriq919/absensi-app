<?php

namespace App\Models;

use App\Observers\AbsensiObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([AbsensiObserver::class])]
class Absensi extends Model
{
    //
    protected $fillable = [
        'name', 'date', 'time', 'status'
    ];

    public function karyawan()
    {
        return $this->hasOne(Karyawan::class, 'nama', 'name');
    }
}
