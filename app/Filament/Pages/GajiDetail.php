<?php

namespace App\Filament\Pages;

use App\Models\Absensi;
use App\Models\Gaji;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Pages\Page;

class GajiDetail extends Page
{
    protected static ?string $routeName = 'filament.admin.pages.gaji-detail';
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.gaji-detail';

    public $events = [];
    public $gaji;

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

        $bolosDates = collect();
        foreach ($allDates as $date) {
            $tanggal = $date->toDateString();
            if (!in_array($tanggal, $checkinDatesRaw)) {
                $bolosDates->push([
                    'title' => '❌ Tidak Masuk',
                    'start' => $tanggal,
                    'color' => 'red',
                ]);
            }
        }

        $this->events = $checkinDates->merge($bolosDates)->values();

        $this->gaji = Gaji::where('karyawan_id', $user->id)
            ->whereMonth('tanggal_gaji', $bulan)
            ->whereYear('tanggal_gaji', $tahun)
            ->first();
    }

    protected function getViewData(): array
    {
        return [
            'events' => $this->events,
            'gaji' => $this->gaji,
        ];
    }
}
