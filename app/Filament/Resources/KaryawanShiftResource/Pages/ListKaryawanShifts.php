<?php

namespace App\Filament\Resources\KaryawanShiftResource\Pages;

use App\Filament\Resources\KaryawanShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKaryawanShifts extends ListRecords
{
    protected static string $resource = KaryawanShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Shift Karyawan')->icon('heroicon-o-plus'),
        ];
    }
}
