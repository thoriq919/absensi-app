<?php

namespace App\Filament\Widgets;

use App\Models\Cuti;
use App\Models\Karyawan;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class CutiTable extends BaseWidget
{
    public function table(Table $table): Table
    {
        $karyawanId = Karyawan::where('user_id', Auth::user()->id)->first()->id;
        return $table
            ->heading('Status Pengajuan Cuti')
            ->description('Daftar pengajuan cuti anda.')
            ->query(
                Cuti::orderBy('tanggal_mulai', 'desc')
                    ->where('karyawan_id', $karyawanId)
            )
            ->columns([
                TextColumn::make('tanggal')
                ->label('Tanggal')
                    ->getStateUsing(function ($record) {
                        if ($record->tanggal_mulai === $record->tanggal_selesai) {
                            return $record->tanggal_mulai;
                        }

                        return $record->tanggal_mulai . ' sampai ' . $record->tanggal_selesai;
                    }),
                TextColumn::make('keterangan')->label('Keterangan'),
                Tables\Columns\TextColumn::make('status_pengajuan')
                    ->label('Status')
                    ->icon(fn (string $state): string => match ($state){
                        'approve' => 'icon-confirm-circle',
                        'reject' => 'icon-close-circle',
                        default  => 'icon-loading'
                    })
                    ->iconColor(fn (string $state): string => match ($state) {
                        'approve' => 'success',
                        'reject' => 'danger',
                        default => 'info'
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ]);
    }
}
