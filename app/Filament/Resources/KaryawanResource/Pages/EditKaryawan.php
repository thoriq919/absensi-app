<?php

namespace App\Filament\Resources\KaryawanResource\Pages;

use App\Filament\Resources\KaryawanResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EditKaryawan extends EditRecord
{
    protected static string $resource = KaryawanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            

        ];
    }

    protected function afterSave(): void
    {
        $user = $this->record->user; 
        $password = $this->data['user']['password'] ?? null; 

        if ($password) {
            $user->password = $password; 
            $user->save(); 
        }
    }
}
