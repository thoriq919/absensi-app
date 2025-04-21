<?php

namespace App\Filament\Resources\GajiResource\Pages;

use App\Events\GajiNotification;
use App\Filament\Resources\GajiResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
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
            ->label('Hitung Gaji')
            ->icon('heroicon-o-calculator')
            ->form([
                DatePicker::make('month')
                    ->label('Periode (Bulan dan Tahun)')
                    ->required()
                    ->displayFormat('Y-m')
                    ->format('Y-m')
                    ->maxDate(now()->subMonth()->endOfMonth())
                    ->default(now()->subMonth()->startOfMonth()),
            ])
            ->action(function (array $data) {
                try {
                    $monthInput = $data['month'];
                    if (!preg_match('/^\d{4}-\d{2}$/', $monthInput)) {
                        Notification::make()
                            ->title('Error')
                            ->body('Format periode harus YYYY-MM.')
                            ->danger()
                            ->send();
                        return;
                    }
                    [$year, $month] = explode('-', $monthInput);
                    $year = (int)$year;
                    $month = (int)$month;

                    $exitCode = Artisan::call('payroll:calculate', [
                        'month' => $month,
                        'year' => $year,
                    ]);

                    if ($exitCode === 0) {
                        Notification::make()
                            ->title('Sukses')
                            ->body("Perhitungan gaji untuk bulan $month berhasil.")
                            ->success()
                            ->send();

                            event(new GajiNotification(
                                'Perhitungan Gaji Selesai',
                                "Perhitungan gaji untuk periode $month telah selesai.",
                                'success'
                            ));
                        } else {
                        $output = Artisan::output();
                        Log::error('Payroll calculate failed: ' . $output);
                        Notification::make()
                            ->title('Error')
                            ->body('Gagal menghitung gaji: ' . $output)
                            ->danger()
                            ->send();
                    }
                } catch (\Exception $e) {
                    Log::error('Error running payroll:calculate: ' . $e->getMessage());
                    Notification::make()
                        ->title('Error')
                        ->body('Terjadi kesalahan: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Perhitungan Gaji')
            ->modalDescription('Apakah Anda yakin ingin menghitung gaji untuk periode ini?')
            ->visible(fn () => auth()->user()->hasRole('admin')),
        ];
    }
}
