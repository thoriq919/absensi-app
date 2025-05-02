<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AbsensiTable extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->heading('Absensi Hari Ini')
            ->description('Daftar karyawan yang melakukan absen hari ini.')
            ->query(
                Absensi::orderBy('date','desc')
                    ->orderBy('time', 'desc')
                    ->where('date', Carbon::today()->toDateString())
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('date')->label('Date'),
                TextColumn::make('time')->label('Time'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'check-in' => 'success',
                        'check-out' =>  'danger'
                    })
                    ->formatStateUsing(fn (string $state) => match (strtolower($state)) {
                        'check-in' => 'Check In',
                        'check-out' => 'Check Out',
                        default => ucfirst($state),
                    })
            ])
            ->paginated(false)
            ->deferLoading();
    }
}
