<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CutiResource\Pages;
use App\Filament\Resources\CutiResource\RelationManagers;
use App\Models\Cuti;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CutiResource extends Resource
{
    protected static ?string $model = Cuti::class;

    public static ?string $pluralLabel = 'Cuti';

    protected static ?string $navigationIcon = 'icon-flight';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('karyawan');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['karyawan','admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\Select::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama') 
                    ->required()
                    ->hidden(fn() => Auth::user()->hasRole('karyawan'))
                    ->columnSpan('full'),
                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->displayFormat('d/m/Y'),
                Forms\Components\DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->displayFormat('d/m/Y'),
                Forms\Components\FileUpload::make('dokumen_pendukung')
                    ->label('Dokumen Pendukung(Opsional)')
                    ->directory('dokumen-pendukung')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(10240) 
                    ->multiple(false)
                    ->columnSpan('full')
                    ->getUploadedFileNameForStorageUsing(function ($file) {
                        return uniqid() . '.' . $file->getClientOriginalExtension();
                    }),
                Forms\Components\Select::make('keterangan')
                    ->label('Keterangan')
                    ->options([
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
                    ])
                    ->required(),
                Forms\Components\Select::make('status_pengajuan')
                    ->label('Status Pengajuan')
                    ->options([
                        'pending' => 'Pending',
                        'approve' => 'Approve',
                        'reject' => 'Reject'
                    ])
                    ->required()
                    ->default('pending')
                    ->hidden(fn () => Auth::user()->hasRole('karyawan'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                //
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->getStateUsing(function ($record) {
                        if ($record->tanggal_mulai === $record->tanggal_selesai) {
                            return $record->tanggal_mulai;
                        }

                        return $record->tanggal_mulai . ' sampai ' . $record->tanggal_selesai;
                    }),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('dokumen_pendukung')
                    ->label('Dokumen')
                    ->formatStateUsing(fn (?string $state): string => $state 
                        ? \Filament\Tables\Actions\Action::make('view')
                            ->label('Lihat')
                            ->icon('heroicon-o-eye')
                            ->url(fn () => Storage::url($state))
                            ->openUrlInNewTab()
                            ->extraAttributes(['class' => 'text-blue-500'])
                            ->toHtml()
                        : '-')
                    ->html(),
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
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (auth()->check() && auth()->user()->hasRole('karyawan')) {
                    $query->where('karyawan_id', auth()->user()->karyawan->id);
                }
                $query->orderBy('updated_at', 'desc');
            })
            ->filters([
                Tables\Filters\SelectFilter::make('status_pengajuan')
                    ->label('Status Pengajuan')
                    ->options([
                        'pending' => 'Pending',
                        'approve' => 'Approve',
                        'reject' => 'Reject',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('status_pengajuan', $data['value']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(function ($record) {
                        $user = Auth::user();

                        if ($user?->hasRole('karyawan')) {
                            return $record->status_pengajuan === 'pending';
                        }

                        return true; 
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(function ($record) {
                        $user = Auth::user();

                        if ($user?->hasRole('karyawan')) {
                            return $record->status_pengajuan === 'pending';
                        }

                        return true; 
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => 
                           Auth::user()?->hasRole('admin')
                        ),
                ]),
                Tables\Actions\BulkAction::make('update_status')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Forms\Components\Select::make('status_pengajuan')
                                ->label('Status Baru')
                                ->options([
                                    'approve' => 'Approve',
                                    'reject' => 'Reject',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'status_pengajuan' => $data['status_pengajuan'],
                                ]);
                            });
                        })
                        ->visible(fn () => Auth::user()?->hasRole('admin'))
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListCutis::route('/'),
            'create' => Pages\CreateCuti::route('/create'),
            'edit' => Pages\EditCuti::route('/{record}/edit'),
        ];
    }
}
