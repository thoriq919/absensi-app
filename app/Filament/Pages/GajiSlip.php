<?php

namespace App\Filament\Pages;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Gaji;
use App\Models\Karyawan;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class GajiSlip extends Page
{
    protected static ?string $routeName = 'filament.admin.pages.gaji-slip';
    protected static string $view = 'filament.pages.gaji-slip';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Slip Gaji';
    protected static ?string $title = 'Cetak Slip Gaji';

    public $tanggal_gaji;
    public $gaji_pokok;
    public $karyawan;
    public $hadir = 0;
    public $izin = 0;
    public $sakit = 0;
    public $alpha = 0;
    public $showSlip = false;
    public $potongan = 0;
    public $bonus = 0;
    public $lembur = 0;
    public $gaji_bersih = 0;
    public $total_hari_kerja;
    
    private $record;
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    
    public function mount():void
    {
        $this->record = Gaji::with('karyawan')->find(session('slip_gaji_id'));
        $this->karyawan = Karyawan::find($this->record->karyawan_id);
        $this->generateSlip();
    }

    private function generateSlip()
    {
        $tanggal = Carbon::parse($this->record->tanggal_gaji);
        $month = $tanggal->month;
        $year = $tanggal->year;

        // Hitung kehadiran
        $this->hitungKehadiran($month, $year);
        $this->hitungTotalGaji($month, $year);
        
        $this->showSlip = true;
    }

    private function hitungTotalGaji($month, $year)
    {
        $gaji_harian = 40000;

        $this->total_hari_kerja = cal_days_in_month(CAL_GREGORIAN, $month,$year);
        
        $this->gaji_pokok = $this->total_hari_kerja * $gaji_harian;
        
        if ($this->total_hari_kerja == $this->hadir) {
            $this->bonus = 100000;
        }
        $this->potongan = $this->record->potongan;
        $this->lembur = $this->record->lembur;
        $this->gaji_bersih = $this->record->gaji_bersih;
        
    }

    private function hitungKehadiran($month, $year)
    {
        // Ambil semua tanggal dalam bulan itu
        $startDate = Carbon::createFromDate($year, $month, 1);
        $now = Carbon::now();
        $endDate = $startDate->copy()->endOfMonth();

        if ($startDate->month === $now->month && $startDate->year === $now->year) {
            $endDate = $now->copy(); 
        }
        
        $totalDays = $endDate->day;
        
        // Hadir - cek absensi dengan check-in dan check-out
        $absensi = Absensi::where('name', $this->record->karyawan->nama)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy('date')
            ->filter(function ($items) {
                $statuses = $items->pluck('status')->unique()->toArray();

                if (in_array('check-in', $statuses) && in_array('check-out', $statuses)) {
                    $checkIn = $items->firstWhere('status', 'check-in');
                    $checkOut = $items->firstWhere('status', 'check-out');
                    if ($checkIn && $checkOut) {
                        $inTime = Carbon::parse($checkIn->time);
                        $outTime = Carbon::parse($checkOut->time);
                        $diff = $inTime->diffInHours($outTime);;
                        return $diff > 4; 
                    }
                }
                return false;
            });

        $this->hadir = $absensi->count();

        // Izin - dari tabel cuti dengan status approve dan keterangan izin
        $cutis = Cuti::where('karyawan_id', $this->record->karyawan->id)
            ->where('status_pengajuan', 'approve')
            ->where('keterangan', 'izin')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_mulai', [$startDate, $endDate])
                      ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('tanggal_mulai', '<=', $startDate)
                            ->where('tanggal_selesai', '>=', $endDate);
                      });
            })
            ->get();

        $izinDates = collect();
        foreach ($cutis as $cuti) {
            $mulai = Carbon::parse($cuti->tanggal_mulai);
            $selesai = Carbon::parse($cuti->tanggal_selesai);
            
            $rangeStart = $mulai->lt($startDate) ? $startDate : $mulai;
            $rangeEnd = $selesai->gt($endDate) ? $endDate : $selesai;
            
            $range = $rangeStart->daysUntil($rangeEnd->addDay());
            foreach ($range as $day) {
                if ($day->month == $month && $day->year == $year) {
                    $izinDates->push($day->toDateString());
                }
            }
        }
        $this->izin = $izinDates->unique()->count();

        $sakitCutis = Cuti::where('karyawan_id', $this->record->karyawan->id)
            ->where('status_pengajuan', 'approve')
            ->where('keterangan', 'sakit')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_mulai', [$startDate, $endDate])
                      ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('tanggal_mulai', '<=', $startDate)
                            ->where('tanggal_selesai', '>=', $endDate);
                      });
            })
            ->get();

        $sakitDates = collect();
        foreach ($sakitCutis as $cuti) {
            $mulai = Carbon::parse($cuti->tanggal_mulai);
            $selesai = Carbon::parse($cuti->tanggal_selesai);
            
            $rangeStart = $mulai->lt($startDate) ? $startDate : $mulai;
            $rangeEnd = $selesai->gt($endDate) ? $endDate : $selesai;
            
            $range = $rangeStart->daysUntil($rangeEnd->addDay());
            foreach ($range as $day) {
                if ($day->month == $month && $day->year == $year) {
                    $sakitDates->push($day->toDateString());
                }
            }
        }
        $this->sakit = $sakitDates->unique()->count();

        $this->alpha = max($totalDays - $this->hadir - $this->izin - $this->sakit, 0);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Cetak Slip Gaji';
    }
}