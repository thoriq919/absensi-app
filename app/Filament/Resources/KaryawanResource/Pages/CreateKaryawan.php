<?php

namespace App\Filament\Resources\KaryawanResource\Pages;

use App\Filament\Resources\KaryawanResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class CreateKaryawan extends CreateRecord
{
    protected static string $resource = KaryawanResource::class;

    public function getTitle(): string
    {
        return 'Karyawan';
    }

    public function getBreadcrumbs(): array
    {
        return [
            KaryawanResource::getUrl() => KaryawanResource::$pluralLabel,
            '#' => 'Tambah Karyawan',            
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        $userData = $data['user'];
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        $karyawanData = $data;
        unset($karyawanData['user']);
        $karyawanData['user_id'] = $user->id;

        return static::getModel()::create($karyawanData);
    }
}
