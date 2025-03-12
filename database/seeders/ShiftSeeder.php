<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $shifts = [
            [
                'nama' => 'Pagi',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '15:00:00',
                'deskripsi' => 'Shift pagi dari jam 8 pagi hingga 3 sore',
            ],
            [
                'nama' => 'Sore',
                'jam_mulai' => '15:00:00',
                'jam_selesai' => '22:00:00',
                'deskripsi' => 'Shift sore dari jam 3 sore hingga 10 malam',
            ],
        ];

        foreach ($shifts as $shiftData) {
            Shift::updateOrCreate($shiftData);
        }
    }
}
