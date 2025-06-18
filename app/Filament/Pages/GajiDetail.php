<?php

namespace App\Filament\Pages;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Gaji;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Pages\Page;

class GajiDetail extends Page
{
    protected static ?string $routeName = 'filament.admin.pages.gaji-detail';
    
    protected static string $view = 'filament.pages.gaji-detail';

    public $events = [];
    private $record;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $id = request()->get('record');
        $this->record = Gaji::findOrFail($id);
        $user = $this->record->karyawan;
        $bulan = Carbon::parse($this->record->tanggal_gaji)->month;
        $tahun = Carbon::parse($this->record->tanggal_gaji)->year;

        $checkinDatesRaw = Absensi::where('name', $user->nama)
            ->where('status', 'check-in')
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->select('date') 
            ->distinct()     
            ->pluck('date')  
            ->map(fn ($date) => $date) 
            ->toArray();

        $startOfMonth = Carbon::createFromDate($tahun, $bulan, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $allDates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $checkinDates = collect($checkinDatesRaw)->map(fn ($date) => [
            'title' => '✅ Hadir',
            'start' => $date,
            'color' => 'green',
        ]);

        
        $cutiDates = collect();
        
        $cutis = Cuti::where('karyawan_id', $user->id)
            ->where('status_pengajuan', 'approve')
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('tanggal_selesai', [$startOfMonth, $endOfMonth]);
            })
            ->get();

        foreach ($cutis as $cuti) {
            $periode = CarbonPeriod::create($cuti->tanggal_mulai, $cuti->tanggal_selesai);
            foreach ($periode as $date) {
                if ($date->month == $bulan && $date->year == $tahun && $date->lte(now())) {
                    $cutiDates->push([
                        'title' => $cuti->keterangan === 'izin' ? '🟡 Izin' : '🟠 Sakit',
                        'start' => $date->toDateString(),
                        'color' => $cuti->keterangan === 'izin' ? 'orange' : '#ff9999',
                    ]);
                }
            }
        }

        $cutiDatesRaw = $cutiDates->pluck('start')->toArray();
        
        $bolosDates = collect();
        foreach ($allDates as $date) {
            $tanggal = $date->toDateString();
            if (
                !in_array($tanggal, $checkinDatesRaw) && 
                !in_array($tanggal, $cutiDatesRaw) &&
                $date->lte(now())
            ) {
                $bolosDates->push([
                    'title' => '❌ Tidak Masuk',
                    'start' => $tanggal,
                    'color' => 'red',
                ]);
            }
        }

        $this->events = $checkinDates
        ->merge($cutiDates)
        ->merge($bolosDates)
        ->sortBy('start')
        ->values();
    }

    protected function getViewData(): array
    {
        return [
            'events' => $this->events,
            'gaji' => $this->record,
        ];
    }
}
