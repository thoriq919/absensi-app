<?php

namespace App\Observers;

use App\Models\absensi;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AbsensiObserver
{
    /**
     * Handle the absensi "created" event.
     */
    public function created(absensi $absensi): void
    {
        //
        Notification::make()
            ->title("Notifikasi Absen")
            ->body("{$absensi->name} melakukan {$absensi->status} pada {$absensi->date} pukul {$absensi->time}.")
            ->sendToDatabase(User::find(1));
    }

    /**
     * Handle the absensi "updated" event.
     */
    public function updated(absensi $absensi): void
    {
        //
        
    }

    /**
     * Handle the absensi "deleted" event.
     */
    public function deleted(absensi $absensi): void
    {
        //
    }

    /**
     * Handle the absensi "restored" event.
     */
    public function restored(absensi $absensi): void
    {
        //
    }

    /**
     * Handle the absensi "force deleted" event.
     */
    public function forceDeleted(absensi $absensi): void
    {
        //
    }
}
