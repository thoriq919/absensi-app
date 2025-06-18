<?php

namespace App\Filament\Resources;

use App\Events\GajiCreated;
use App\Events\GajiNotification;
use App\Filament\Resources\GajiResource\Pages;
use App\Models\Gaji;
use App\Notifications\GajiValidated;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GajiResource extends Resource
{
    protected static ?string $model = Gaji::class;

    public static ?string $pluralLabel = 'Gaji';

    protected static ?string $navigationIcon = 'icon-payment';

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['karyawan']);
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
                //
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_gaji')
                    ->label('Bulan')
                    ->date('F Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('Detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.pages.gaji-detail') . '?record=' . $record->id)
                    ->openUrlInNewTab(),
                Action::make('payroll_print')
                    ->label('Cetak Slip')
                    ->icon('heroicon-o-printer')
                    ->action(function ($record) {
                        session(['slip_gaji_id' => $record->id]);
                        return redirect()->route('filament.admin.pages.gaji-slip');
                    })
                    ->openUrlInNewTab(false),
            ])            
            ->filters([
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (auth()->check() && auth()->user()->hasRole('karyawan')) {
                    $query->where('karyawan_id', auth()->user()->karyawan->id);
                }
                $query->orderBy('created_at', 'desc');
            })
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListGajis::route('/'),
        ];
    }
}
