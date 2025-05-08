<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ArrivedLateTable extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->heading('Absensi Hari Ini')
            ->description('Daftar karyawan yang melakukan absen hari ini.')
            ->query(
               Absensi::with('karyawan')
            )
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('date')->label('Date'),
                TextColumn::make('time')->label('Time'),
            ]);
    }
}
