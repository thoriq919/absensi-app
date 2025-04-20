<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    public static ?string $pluralLabel = 'Shift';

    protected static ?string $navigationIcon = 'icon-shift';

    protected static ?string $navigationGroup = 'Jadwal'; 
    
    protected static bool $isCollapsibleNavigationGroup = false;

    protected static ?string $emptyStateHeading = 'Tidak ada data';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Formulir Shift')
                ->schema([
                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Shift')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Grid::make()
                        ->columns([
                            'default' => 1, 
                            'sm' => 2,     
                        ])
                        ->schema([
                            Forms\Components\TimePicker::make('jam_mulai')
                                ->label('Jam Mulai')
                                ->required(),
                            Forms\Components\TimePicker::make('jam_selesai')
                                ->label('Jam Selesai')
                                ->required(),
                        ]),
                    Forms\Components\Textarea::make('deskripsi')
                        ->label('Deskripsi')
                        ->nullable()
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Shift')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jam_mulai')
                    ->label('Jam Mulai')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('H:i')),
                Tables\Columns\TextColumn::make('jam_selesai')
                    ->label('Jam Selesai')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('H:i')),
                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->default('-')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}
