<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiResource\Pages;
use App\Models\Absensi;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    public static ?string $pluralLabel = 'Absen Karyawan';

    protected static ?string $navigationIcon = 'icon-present';

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('check_in_time')
                    ->label('Jam Check-in')
                    ->state(function ($record) {
                        $checkIn = \App\Models\Absensi::where('name', $record->name)
                            ->where('date', $record->date)
                            ->where('status', 'check-in')
                            ->first();
                        
                        return $checkIn ? $checkIn->time : '-';
                    }),

                TextColumn::make('check_out_time')
                    ->label('Jam Check-out')
                    ->state(function ($record) {
                        $checkOut = \App\Models\Absensi::where('name', $record->name)
                            ->where('date', $record->date)
                            ->where('status', 'check-out')
                            ->first();
                        
                        return $checkOut ? $checkOut->time : '-';
                    }),

                TextColumn::make('attendance_status')
                    ->label('Status Kehadiran')
                    ->state(function ($record) {
                        $checkIn = Absensi::where('name', $record->name)
                        ->where('date', $record->date)
                        ->where('status', 'check-in')
                        ->first();
                    
                        $checkOut = \App\Models\Absensi::where('name', $record->name)
                            ->where('date', $record->date)
                            ->where('status', 'check-out')
                            ->first();
                        
                        if ($checkIn && $checkOut) {
                            $jamMasuk = Carbon::parse($checkIn->time);
                            $jamPulang = Carbon::parse($checkOut->time);
                        
                            $durasi = $jamMasuk->diffInHours($jamPulang);
                        
                            if ($durasi < 4) {
                                return 'Tidak Valid';
                            }
                        
                            return 'Hadir';
                        } elseif ($checkIn || $checkOut) {
                            return 'Tidak Lengkap';
                        } else {
                            return 'Tidak Hadir';
                        }
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Tidak Lengkap' => 'warning',
                        'Tidak Hadir' => 'danger',
                        default => 'secondary',
                    }),

                TextColumn::make('total_minutes')
                    ->label('Total Masuk')
                    ->state(function ($record) {
                        $checkIn = Absensi::where('name', $record->name)
                            ->where('date', $record->date)
                            ->where('status', 'check-in')
                            ->first();

                        $checkOut = Absensi::where('name', $record->name)
                            ->where('date', $record->date)
                            ->where('status', 'check-out')
                            ->first();

                        if ($checkIn && $checkOut) {
                            $checkInTime = \Carbon\Carbon::parse($record->date . ' ' . $checkIn->time);
                            $checkOutTime = \Carbon\Carbon::parse($record->date . ' ' . $checkOut->time);

                            $diff = $checkInTime->diff($checkOutTime);
                            $hours = $diff->h + ($diff->days * 24); 
                            $minutes = $diff->i;
                            $parts = [];

                            if ($hours > 0) {
                                $parts[] = "{$hours} jam";
                            }

                            if ($minutes > 0) {
                                $parts[] = "{$minutes} menit";
                            }

                            return count($parts) > 0 ? implode(' ', $parts) : '0 menit';
                        }

                        return '-';
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $distinctRecords = DB::table('absensis')
                    ->select('date', 'name')
                    ->distinct()
                    ->get();
                
                $ids = [];
                foreach ($distinctRecords as $record) {
                    $id = \App\Models\Absensi::where('date', $record->date)
                        ->where('name', $record->name)
                        ->first()?->id;
                    if ($id) {
                        $ids[] = $id;
                    }
                }
                
                $query->whereIn('id', $ids);

                if (auth()->check() && auth()->user()->hasRole('karyawan')) {
                    $query->where('name', auth()->user()->karyawan->nama);
                }
            })
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                    DatePicker::make('date')
                            ->label('Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date'],
                                fn (Builder $query, $date): Builder => $query->where('date', $date),
                            );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('10s')
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsensis::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit' => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }
}
