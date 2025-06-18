<?php

namespace App\Console\Commands;

use App\Events\GajiCreated;
use App\Events\GajiNotification;
use App\Models\Absensi;
use App\Models\Gaji;
use App\Models\Karyawan;
use App\Models\KaryawanShift;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculatePayroll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    
     protected $signature = 'payroll:calculate 
     {--tanggal= : Tanggal full (format Y-m-d)} 
     {--bulan= : Bulan (format 01 - 12)} 
     {--tahun= : Tahun (format 4 digit)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate payroll for a given date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tanggal = $this->option('tanggal');
        $month = $this->option('bulan');
        $year = $this->option('tahun');

        if ($tanggal) {

        }elseif($month && $year){
            $this->calculatePerMonth($month, $year);
        }else{
            $this->error('Isi --tanggal atau --bulan dan --tahun');
        }
    }

    private function calculdatePerDay($tanggal)
    {

    }

    private function calculatePerMonth($month, $year)
    {
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month,$year);

        $karyawanShifts = KaryawanShift::where('tanggal_mulai', '<=', $endDate)
        ->where('tanggal_selesai', '>=', $startDate)
        ->whereHas('shift')
        ->whereHas('karyawan')
        ->get();

        $totalGajiBersih = 0;
        $totalPotongan = 0;
        $totalLembur = 0;

        foreach ($karyawanShifts as $ks)
        {
            $karyawan = Karyawan::find($ks->karyawan_id);
            $karyawanName = $karyawan->nama;
            $shift = Shift::find($ks->shift_id);

            $checkins = Absensi::where('name', $karyawanName)
                ->whereDate('date', '>=', $startDate)
                ->where('status', 'check-in')
                ->get()
                ->keyBy('date');

            $checkouts = Absensi::where('name', $karyawanName)
                ->whereDate('date', '<=', $endDate)
                ->where('status', 'check-out')
                ->get()
                ->keyBy('date');

            $jamMulaiShift = Carbon::parse($shift->jam_mulai);
            $jamSelesaiShift = Carbon::parse($shift->jam_selesai);

            $jumlahHadirValid = 0;
            $gajiKaryawan = 0;
            $potonganKaryawan = 0;
            $lemburKaryawan = 0;

            $hariKerja = $startDate->copy();
            while ($hariKerja <= $endDate) {
                $tanggal = $hariKerja->toDateString();

                $checkin = $checkins[$tanggal] ?? null;
                $checkout = $checkouts[$tanggal] ?? null;

                if ($checkin && $checkout) {
                    $jamMasuk = Carbon::parse($checkin->time);
                    $jamPulang = Carbon::parse($checkout->time);

                    $durasiKerja = $jamMasuk->diffInMinutes($jamPulang);

                    // Skip hari jika kerja < 4 jam
                    if ($durasiKerja < 240) {
                        $hariKerja->addDay();
                        continue;
                    }

                    $jumlahHadirValid++;

                    $potonganHariItu = 0;
                    $bonusLembur = 0;

                    // Telat
                    if ($jamMasuk->gt($jamMulaiShift)) {
                        $menitTelat = $jamMulaiShift->diffInMinutes($jamMasuk);
                        $potonganHariItu += floor($menitTelat / 60) * 5000;
                    }

                    // Lembur
                    if ($jamPulang->gt($jamSelesaiShift)) {
                        $menitLembur = $jamSelesaiShift->diffInMinutes($jamPulang);
                        $bonusLembur = floor($menitLembur / 60) * 5000;
                    }

                    // Tambah gaji harian dan akumulasi
                    $gajiKaryawan += 40000;
                    $potonganKaryawan += $potonganHariItu;
                    $lemburKaryawan += $bonusLembur;

                } else {
                   
                }

                $hariKerja->addDay();
            }

            // Bonus Kehadiran Full
            if ($jumlahHadirValid === $totalDays) {
                $gajiKaryawan += 100000;
            }

            $totalGajiBersih += $gajiKaryawan;
            $totalPotongan += $potonganKaryawan;
            $totalLembur += $lemburKaryawan;

            Gaji::updateOrCreate(
                [
                    'karyawan_id' => $karyawan->id,
                    'tanggal_gaji' => $endDate->toDateString(),
                ],[
                    'gaji_pokok' => $totalDays * 40000,
                    'tunjangan_kehadiran' => $jumlahHadirValid === $totalDays ? 100000 : 0,
                    'lembur' => $lemburKaryawan,
                    'potongan' => $potonganKaryawan,
                    'gaji_bersih' => ($totalGajiBersih + $lemburKaryawan - $potonganKaryawan),
                    'validated' => 0,
                ]
            );
        }
    }
}