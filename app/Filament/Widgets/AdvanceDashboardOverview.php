<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Karyawan;
use Carbon\Carbon;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class AdvanceDashboardOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        $currentTime = Carbon::now();
        $totalCuti = Cuti::whereBetween('tanggal_mulai', [$today->startOfWeek(), $today->endOfWeek()])
            ->where('status_pengajuan', 'approve')
            ->count();
        $totalAbsen = Absensi::whereDate('created_at',$today)
            ->distinct('name')
            ->count();
        $totalKaryawanOnShift = Karyawan::where('is_active', true)
            ->whereHas('karyawanShift', function ($query) use ($today) {
                $query->where('tanggal_mulai', '<=', $today)
                    ->where('tanggal_selesai', '>=', $today);
            })
            ->whereHas('shift', function($query) use ($currentTime) {
                $query->whereTime('jam_mulai', '<=', $currentTime->toTimeString())
                    ->whereTime('jam_selesai', '>=', $currentTime->toTimeString());
            })
            ->count();
        return [
            Stat::make('Total Karyawan Hadir', $totalAbsen)
                ->icon('heroicon-o-clipboard-document-check')
                ->textColor('gray', 'gray', 'success')
                ->iconPosition('start')
                ->description('Absensi hari ini')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Cuti', $totalCuti)
                ->icon('heroicon-o-calendar-date-range')
                ->textColor('gray', 'gray', 'warning')
                ->iconPosition('end')
                ->description('Cuti minggu ini')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning'),
            Stat::make('Total Karyawan', $totalKaryawanOnShift)
                ->icon('heroicon-o-briefcase')
                ->textColor('gray', 'gray', 'info')
                ->iconPosition('end')
                ->description('Karyawan pada shift saat ini')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('info')
                ->iconColor('info')
        ];
    }
}
