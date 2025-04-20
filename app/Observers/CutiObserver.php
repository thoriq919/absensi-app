<?php

namespace App\Observers;

use App\Models\Cuti;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CutiObserver
{
    /**
     * Handle the Cuti "created" event.
     */
    public function created(Cuti $cuti): void
    {
        //
        $user = Auth::user();

        if ($user->hasRole('karyawan')){
            Notification::make()
                ->title("Pengajuan Cuti")
                ->body($user->karyawan->nama." telah mengajukan cuti")
                ->actions([
                    Action::make('view')
                        ->label('Lihat Detail')
                        ->url(route('filament.admin.resources.cutis.index')),
                ])
                ->sendToDatabase(User::find(1));
        }
    }

    /**
     * Handle the Cuti "updated" event.
     */
    public function updated(Cuti $cuti): void
    {
        //
        if ($cuti->isDirty('status_pengajuan')) {
            $user = Auth::user();

            if ($user && $user->hasRole('admin')) {
                $karyawanUser = $cuti->karyawan?->user;

                if ($karyawanUser) {
                    Notification::make()
                        ->title("Pengajuan Cuti")
                        ->body("Admin telah ".($cuti->status_pengajuan == 'approve' ? 'menyetujui' : 'menolak')." pengajuan cuti pada tanggal ".$cuti->tanggal_mulai." sampai ".$cuti->tanggal_selesai)
                        ->actions([
                            Action::make('view')
                                ->label('Lihat Detail')
                                ->url(route('filament.admin.resources.cutis.index')),
                        ])
                        ->sendToDatabase($karyawanUser);
                }
            }
        }
    }

    /**
     * Handle the Cuti "deleted" event.
     */
    public function deleted(Cuti $cuti): void
    {
        //
    }

    /**
     * Handle the Cuti "restored" event.
     */
    public function restored(Cuti $cuti): void
    {
        //
    }

    /**
     * Handle the Cuti "force deleted" event.
     */
    public function forceDeleted(Cuti $cuti): void
    {
        //
    }
}
