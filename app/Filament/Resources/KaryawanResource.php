<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Filament\Resources\KaryawanResource\Pages\CreateKaryawan;
use App\Filament\Resources\KaryawanResource\RelationManagers;
use App\Models\Karyawan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;

    public static ?string $pluralLabel = 'Karyawan';

    protected static ?string $navigationIcon = 'icon-employee';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Akun')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Nama Pengguna')
                            ->required()
                            ->maxLength(255)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->user?->name)),
                        Forms\Components\TextInput::make('user.email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignorable: fn ($record) => $record?->user)
                            ->maxLength(255)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->user?->email)),
                        Forms\Components\TextInput::make('user.password')
                            ->label('Password')
                            ->password()
                            ->required(fn (Page $livewire) => ($livewire instanceof CreateKaryawan))
                            ->minLength(8)
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                    ])->visibleOn(['create', 'edit']),
                Forms\Components\Section::make('Data Karyawan')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('no_telp')
                            ->label('Nomor Telepon')
                            ->required()
                            ->maxLength(15),
                        Forms\Components\DatePicker::make('tanggal_masuk')
                            ->label('Tanggal Masuk')
                            ->required()
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TextInput::make('rfid_number')
                            ->label('Nomor RFID')
                            ->maxLength(10) 
                            ->unique('karyawans', 'rfid_number', ignorable: fn ($record) => $record)
                            ->readOnly()
                            ->reactive()
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->rfid_number)),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generate_rfid')
                                ->label('Generate Nomor RFID')
                                ->action(function ($set) {
                                    $randomBytes = random_bytes(4);
                                    $hexValue = bin2hex($randomBytes);
                                    $rfid = "0x" . $hexValue;
                                    $set('rfid_number', $rfid);
                                }),
                        ])->visibleOn(['create', 'edit']),
                        Forms\Components\TextInput::make('saldo_cuti')
                            ->label('Saldo Cuti')
                            ->numeric()
                            ->default(2)
                            ->readOnly(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_telp')
                    ->label('Nomor Telepon')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
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
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }
}
