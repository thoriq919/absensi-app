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
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('Detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.pages.gaji-detail') . '?record=' . $record->id)
                    ->openUrlInNewTab(),
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
                    // Tables\Actions\BulkAction::make('validate_gaji')
                    //     ->label('Validasi Gaji')
                    //     ->icon('heroicon-o-check-circle')
                    //     ->requiresConfirmation()
                    //     ->modalHeading('Konfirmasi Validasi Gaji')
                    //     ->modalDescription('Apakah Anda yakin ingin memvalidasi gaji yang dipilih?')
                    //     ->action(function ($records) {
                    //         try {
                    //             DB::transaction(function () use ($records) {
                    //                 $records->each(function ($gaji) {
                    //                     $gaji->update(['validated' => true]);

                    //                     // Broadcast gaji update
                    //                     event(new GajiCreated($gaji));
                                        
                    //                     // Notifikasi real-time
                    //                     event(new GajiNotification(
                    //                         'Gaji Divalidasi',
                    //                         "Gaji untuk {$gaji->karyawan->nama} pada". Carbon::parse($gaji->tanggal_gaji)->format('d m Y')." telah divalidasi oleh " . auth()->user()->name,
                    //                         'success'
                    //                     ));

                    //                     // Notifikasi database untuk pengguna saat ini
                    //                     Notification::make()
                    //                         ->title('Gaji Divalidasi')
                    //                         ->body("Gaji untuk {$gaji->karyawan->nama} pada ". Carbon::parse($gaji->tanggal_gaji)->format('d m Y')."telah divalidasi.")
                    //                         ->success();
                    //                 });
                    //             });

                    //             Notification::make()
                    //                 ->title('Sukses')
                    //                 ->body('Gaji berhasil divalidasi.')
                    //                 ->success()
                    //                 ->send();
                    //         } catch (\Exception $e) {
                    //             Log::error('Error validating gaji: ' . $e->getMessage());
                    //             Notification::make()
                    //                 ->title('Error')
                    //                 ->body('Gagal memvalidasi gaji: ' . $e->getMessage())
                    //                 ->danger()
                    //                 ->send();
                    //         }
                    //     })
                    //     ->deselectRecordsAfterCompletion()
                    //     ->visible(fn () => auth()->user()->hasRole('admin')),
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
