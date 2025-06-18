<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\Gaji;
use App\Models\Karyawan;
use App\Models\KaryawanShift;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculatePayrollPerDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:calculated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hitung gaji harian';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tanggal = Carbon::parse($this->argument('tanggal'))->format('Y-m-d');
        $karyawanShifts = KaryawanShift::whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->get();

        foreach ($karyawanShifts as $ks) {
            $karyawanName = Karyawan::find($ks->karyawan_id)->nama;
            $shift = Shift::find($ks->shift_id);
            $checkin = Absensi::where('name', $karyawanName)
                ->whereDate('date', $tanggal)
                ->where('status', 'check-in')
                ->orderBy('time', 'asc')
                ->first();

            $checkout = Absensi::where('name', $karyawanName)
                ->whereDate('date', $tanggal)
                ->where('status', 'check-out')
                ->orderBy('time', 'desc')
                ->first();
            $jamMulaiShift = Carbon::parse($shift->jam_mulai);
            $jamSelesaiShift = Carbon::parse($shift->jam_selesai);

            // Jika tidak hadir
            if (!$checkin || !$checkout) {
                Gaji::create([
                    'karyawan_id' => $ks->karyawan_id,
                    'tanggal_gaji' => $tanggal,
                    'gaji_pokok' => 0,
                    'tunjangan_kehadiran' => 0,
                    'lembur' => 0,
                    'potongan' => 0,
                    'gaji_bersih' => 0,
                    'validated' => 1,
                ]);
                continue;
            }

            $jamMasuk = Carbon::parse($checkin->time);
            $jamPulang = Carbon::parse($checkout->time);

            $gajiPokok = 40000;
            $potongan = 0;
            $lembur = 0;

            // Telat
            if ($jamMasuk->gt($jamMulaiShift)) {
                $menitTelat = $jamMulaiShift->diffInMinutes($jamMasuk);
                $potongan = ceil($menitTelat / 60) * 5000;
            }
            
            // Lembur
            if ($jamPulang->gt($jamSelesaiShift)) {
                $menitLembur = $jamSelesaiShift->diffInMinutes($jamPulang);
                $lembur = floor($menitLembur / 60) * 5000;
            }

            // Bonus full hadir di bulan itu
            $startOfMonth = Carbon::parse($tanggal)->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::parse($tanggal)->endOfMonth()->format('Y-m-d');
            $jumlahHariKerja = Carbon::parse($tanggal)->daysInMonth;
            $hadirFullBulan = Absensi::where('name', $karyawanName)
                ->where('status', 'check-in')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->selectRaw('DATE(date) as tanggal')
                ->distinct()
                ->count();
            $bonus = ($hadirFullBulan == $jumlahHariKerja) ? 100000 : 0;

            $gajiBersih = $gajiPokok + $lembur + $bonus - $potongan;

            Gaji::updateOrCreate(
                [
                    'karyawan_id' => $ks->karyawan_id,
                    'tanggal_gaji' => $tanggal,
                ],[
                    'gaji_pokok' => $gajiPokok,
                    'tunjangan_kehadiran' => $bonus,
                    'lembur' => $lembur,
                    'potongan' => $potongan,
                    'gaji_bersih' => $gajiBersih,
                    'validated' => 1,
                ]
            );

            $this->info("✅ Gaji karyawan '$karyawanName': Rp" . number_format($gajiBersih, 0, ',', '.'));
        }
    }
}
