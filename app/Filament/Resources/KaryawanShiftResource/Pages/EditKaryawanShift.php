<?php

namespace App\Filament\Resources\KaryawanShiftResource\Pages;

use App\Filament\Resources\KaryawanShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKaryawanShift extends EditRecord
{
    protected static string $resource = KaryawanShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
