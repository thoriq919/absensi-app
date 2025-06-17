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
                    DatePicker::make('tanggal')
                        ->label('Tanggal Gaji')
                        ->required()
                        ->displayFormat('Y-m-d')
                        ->format('Y-m-d'),
                ])
                ->action(function (array $data) {
                    try {
                        $tanggal = $data['tanggal'];
        
                        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                            Notification::make()
                                ->title('Format Salah')
                                ->body('Format tanggal harus YYYY-MM-DD.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $exitCode = Artisan::call('payroll:calculate', [
                            'tanggal' => $tanggal,
                        ]);
        
                        if ($exitCode === 0) {
                            Notification::make()
                                ->title('Sukses')
                                ->body("Gaji berhasil dihitung untuk tanggal $tanggal.")
                                ->success()
                                ->send();
        
                            event(new GajiNotification(
                                'Perhitungan Gaji Selesai',
                                "Gaji untuk tanggal $tanggal berhasil dihitung.",
                                'success'
                            ));
                        } else {
                            $output = Artisan::output();
                            Log::error('Payroll calculate failed: ' . $output);
                            Notification::make()
                                ->title('Gagal')
                                ->body("Perhitungan gagal: $output")
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Log::error('Exception saat hitung gaji: ' . $e->getMessage());
                        Notification::make()
                            ->title('Error')
                            ->body("Terjadi error: " . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Perhitungan Gaji')
                ->modalDescription('Yakin mau hitung gaji untuk tanggal ini?')
                ->visible(fn () => auth()->user()->hasRole('admin')),
        ];         
    }
}
