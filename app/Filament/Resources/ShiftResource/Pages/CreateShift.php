<?php

namespace App\Filament\Resources\ShiftResource\Pages;

use App\Filament\Resources\ShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class CreateShift extends CreateRecord
{
    protected static string $resource = ShiftResource::class;

    public function getTitle(): string
    {
        return 'Shift';
    }

    public function getBreadcrumbs(): array
    {
        return [
            ShiftResource::getUrl() => ShiftResource::$pluralLabel,
            '#' => 'Tambah Shift',            
        ];
    }
}
