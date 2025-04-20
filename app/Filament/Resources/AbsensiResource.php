<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiResource\Pages;
use App\Models\Absensi;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                TextColumn::make('row_number')
                    ->label('No')
                    ->state(
                        static function (\Filament\Tables\Columns\TextColumn $column, $record, $rowLoop) {
                            return $rowLoop->iteration;
                        }
                    ),
                TextColumn::make('date')->label('Tanggal')->date(),
                TextColumn::make('name')->label('Nama')->sortable()->searchable(),
                TextColumn::make('time')->label('Waktu'),
                TextColumn::make('status')
                    ->label('Status')
                    ->icon(fn (string $state): string => match ($state) {
                        'check-out' => 'heroicon-o-arrow-right-start-on-rectangle',
                        default => 'heroicon-o-arrow-right-end-on-rectangle',
                    })
                    ->iconColor(fn (string $state): string => match ($state) {
                        'check-out' => 'danger',
                        default => 'success'
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'check-out' => 'danger',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('created_at')->label('Created At')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Updated At')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(function (Builder $query): Builder{
                return $query
                    ->orderBy('date', 'desc')
                    ->orderBy('time', 'desc');
            })
            ->modifyQueryUsing(function (Builder $query) {
                if (auth()->check() && auth()->user()->hasRole('karyawan')) {
                    $query->where('name', auth()->user()->karyawan->nama);
                }
            })
            ->filters([
                //
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
