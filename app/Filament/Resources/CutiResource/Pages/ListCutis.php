<?php

namespace App\Filament\Resources\CutiResource\Pages;

use App\Filament\Resources\CutiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;

class ListCutis extends ListRecords
{
    protected static string $resource = CutiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajukan Cuti')->icon('heroicon-o-plus'),
        ];
    }
}
