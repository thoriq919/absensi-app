<?php

namespace App\Services;

use App\Events\AttendanceRecorded;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

class FirebaseService
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials'))
            ->withDatabaseUri(config('services.firebase.database_url'));
        $this->database = $factory->createDatabase();
    }

    public function getAttendanceRecords($date = null)
    {
        $path = $date ? "riwayat_absen/{$date}" : 'riwayat_absen';
        return $this->database->getReference($path)->getValue() ?? [];
    }

    public function syncAttendanceToDatabase()
    {
        $records = $this->getAttendanceRecords();
        foreach ($records as $date => $uids) {
            foreach ($uids as $uid => $record) {
                // Cari nama karyawan berdasarkan UID
                $cleanUid = preg_replace('/-\d+$/', '', $uid);
                $karyawan = Karyawan::has('shift')->where('rfid_number', $cleanUid)->first();
                
                if (!$karyawan) {
                    Log::warning("Karyawan dengan UID {$cleanUid} tidak ditemukan.");
                    continue; // Lewati jika karyawan tidak ditemukan
                }

                // Format tanggal dari DD-MM-YYYY ke YYYY-MM-DD
                $dateParts = explode('-', $date);
                if (count($dateParts) !== 3) {
                    Log::warning("Format tanggal {$date} tidak valid.");
                    continue;
                }
                $formattedDate = "{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]}";
                
                Absensi::updateOrCreate([
                    'name' => $karyawan->nama,
                    'date' => $formattedDate,
                    'time' => $record['time'],
                ],[
                    'status' => $record['status'],
                ]);
            }
        }
        event(new AttendanceRecorded());
    }
}
