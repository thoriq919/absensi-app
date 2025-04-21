<?php

namespace App\Console\Commands;

use App\Events\GajiCreated;
use App\Events\GajiNotification;
use App\Models\Absensi;
use App\Models\Gaji;
use App\Models\KaryawanShift;
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
    
    protected $signature = 'payroll:calculate {month} {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate payroll for a given month (YYYY-MM)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $month = (int)$this->argument('month');
        $year = (int)$this->argument('year');
        $hariKerja = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, $hariKerja);
        $karyawanShifts = KaryawanShift::where('tanggal_mulai', '<=', $endDate)
            ->where('tanggal_selesai', '>=', $startDate)
            ->with(['shift', 'karyawan'])
            ->get();
        foreach ($karyawanShifts as $ks) {
            $karyawanId = $ks->karyawan_id;
            $shift = $ks->shift;
            $jamMulai = Carbon::parse($shift->jam_mulai);
            $jamSelesai = Carbon::parse($shift->jam_selesai);
            
            if($ks->karyawan['is_active'] == 0){
                continue;
            }
            
            $namaKaryawan = $ks->karyawan->nama;

            // Ambil absensi karyawan untuk bulan tertentu
            $absensis = Absensi::where('name', $namaKaryawan)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            if($absensis->isEmpty()){
                continue;
            }
            
            // Hitung kehadiran
            $hariHadir = $absensis->groupBy('date')->count();
            $hariTidakHadir = $hariKerja - $hariHadir;
            
            // Tunjangan kehadiran
            $tunjanganKehadiran = ($hariTidakHadir == 0) ? 100000 : 0;
            
            // Potongan ketidakhadiran
            $potonganKetidakhadiran = ($hariTidakHadir > 2) ? ($hariTidakHadir - 2) * 25000 : 0;
            
            // Hitung keterlambatan dan lembur
            $potonganKeterlambatan = 0;
            $totalJamLembur = 0;

            $absensiPerHari = $absensis->groupBy('date');
            foreach ($absensiPerHari as $date => $records) {
                $checkIn = $records->where('status', 'check-in')->first();
                $checkOut = $records->where('status', 'check-out')->first();

                if ($checkIn) {
                    // Keterlambatan
                    $jamCheckIn = Carbon::parse($checkIn->time);
                    $selisihMenit = $jamMulai->diffInMinutes($jamCheckIn, false);
                    if ($selisihMenit > 60) { 
                        $potonganKeterlambatan += 10000;
                    }
                }

                if ($checkOut && $checkIn) {
                    $jamCheckOut = Carbon::parse($checkOut->time);
                    $selisihLembur = $jamCheckOut->diffInMinutes($jamSelesai, false);
                    if ($selisihLembur > 0) {
                        $totalJamLembur += ceil($selisihLembur / 60); 
                    }
                }

                $lembur = $totalJamLembur * 10000;
                
                $potongan = $potonganKetidakhadiran + $potonganKeterlambatan;
                
                $gajiPokok = 1200000;
                $gajiBersih = $gajiPokok + $tunjanganKehadiran + $lembur - $potongan;
                Log::info('creating');
                DB::transaction(function () use ($karyawanId, $endDate, $gajiPokok, $tunjanganKehadiran, $lembur, $potongan, $gajiBersih, $namaKaryawan) {
                    $gaji = Gaji::updateOrCreate(
                        [
                            'karyawan_id' => $karyawanId,
                            'tanggal_gaji' => $endDate,
                        ],
                        [
                            'gaji_pokok' => $gajiPokok,
                            'tunjangan_kehadiran' => $tunjanganKehadiran,
                            'lembur' => $lembur,
                            'potongan' => $potongan,
                            'gaji_bersih' => $gajiBersih,
                            'validated' => 0,
                        ]
                    );

                    // Broadcast gaji update
                    event(new GajiCreated($gaji));

                    // Notifikasi real-time
                    event(new GajiNotification(
                        'Gaji Dihitung',
                        "Gaji untuk $namaKaryawan pada {$endDate} telah dihitung.",
                        'success'
                    ));
                });

                $this->info("Gaji untuk $namaKaryawan ($month) berhasil dihitung.");
            }
        }
    }
}