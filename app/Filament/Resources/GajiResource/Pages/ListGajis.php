<?php

namespace App\Filament\Resources\GajiResource\Pages;

use App\Events\GajiNotification;
use App\Filament\Resources\GajiResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ListGajis extends ListRecords
{
    protected static string $resource = GajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calculate_payroll')
                ->label('Hitung Gaji Bulanan')
                ->icon('heroicon-o-calculator')
                ->form([
                    Select::make('bulan')
                        ->label('Bulan')
                        ->required()
                        ->options(array_combine(
                            range(1, 12),
                            array_map(fn($m) => now()->startOfYear()->month($m)->translatedFormat('F'), range(1, 12))
                        ))
                        ->default(now()->format('m')),

                    Select::make('tahun')
                        ->label('Tahun')
                        ->required()
                        ->options(
                            collect(range(now()->year, now()->year - 10))->mapWithKeys(fn($y) => [$y => $y])
                        )
                        ->default(now()->year),
                ])
                ->action(function (array $data) {
                    $bulan = str_pad($data['bulan'], 2, '0', STR_PAD_LEFT);
                    $tahun = $data['tahun'];
                    $namaBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');

                    $exitCode = Artisan::call('payroll:calculate', [
                        '--bulan' => $bulan,
                        '--tahun' => $tahun,
                    ]);

                    Notification::make()
                        ->title($exitCode === 0 ? 'Selesai' : 'Gagal')
                        ->body($exitCode === 0
                            ? "✅ Gaji berhasil dihitung untuk {$namaBulan} {$tahun}."
                            : "❌ Gagal menghitung gaji untuk {$namaBulan} {$tahun}.")
                        ->{$exitCode === 0 ? 'success' : 'danger'}()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Hitung Gaji')
                ->modalDescription('Yakin ingin menghitung gaji untuk bulan dan tahun ini?')
                ->modalSubmitActionLabel('Proses')
                ->modalCancelActionLabel('Batal')
                ->visible(fn () => auth()->user()->hasRole('admin')),
        ];         
    }
}
