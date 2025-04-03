<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanShiftResource\Pages;
use App\Filament\Resources\KaryawanShiftResource\RelationManagers;
use App\Models\KaryawanShift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KaryawanShiftResource extends Resource
{
    protected static ?string $model = KaryawanShift::class;

    public static ?string $pluralLabel = 'Shift Karyawan';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Jadwal'; 
    protected static bool $isCollapsibleNavigationGroup = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Formulir Shift Karyawan')
                ->schema([
                    Forms\Components\Select::make('karyawan_id')
                        ->label('Karyawan')
                        ->relationship('karyawan', 'nama') 
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('shift_id')
                        ->label('Shift')
                        ->relationship('shift', 'nama') 
                        ->required()
                        ->searchable(),
                    Forms\Components\DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->required()
                        ->displayFormat('d/m/Y'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift.nama')
                    ->label('Shift')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
                Tables\Filters\SelectFilter::make('karyawan')
                    ->relationship('karyawan', 'nama'),
            ])
            ->actions([
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
            'index' => Pages\ListKaryawanShifts::route('/'),
            'create' => Pages\CreateKaryawanShift::route('/create'),
            'edit' => Pages\EditKaryawanShift::route('/{record}/edit'),
        ];
    }
}
