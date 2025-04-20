<?php

namespace App\Filament\Resources\CutiResource\Pages;

use App\Filament\Resources\CutiResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCuti extends CreateRecord
{
    protected static string $resource = CutiResource::class;

    public function mutateFormDataBeforeCreate(array $data):array
    {
        $user = Auth::user();

        if($user->hasRole('karyawan')){
            $data['karyawan_id'] = $user->karyawan->id;
        }
        
        return $data;
    }
}
