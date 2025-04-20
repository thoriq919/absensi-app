<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAbsensis extends ListRecords
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Lakukan Absen')->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderScripts(): array
    {
        return [
            'attendance-sync' => <<<JS
                window.Echo.channel('attendance')
                    .listen('.attendance.synced', () => {
                        window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).reload();
                    });
            JS,
        ];
    }
}
