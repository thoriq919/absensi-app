<?php

namespace App\Filament\Resources\KaryawanShiftResource\Pages;

use App\Filament\Resources\KaryawanShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKaryawanShift extends CreateRecord
{
    protected static string $resource = KaryawanShiftResource::class;

    public function getTitle(): string
    {
        return 'Shift Karyawan';
    }

    public function getBreadcrumbs(): array
    {
        return [
            KaryawanShiftResource::getUrl() => KaryawanShiftResource::$pluralLabel,
            '#' => 'Tambah Shift Karyawan',            
        ];
    }
}
