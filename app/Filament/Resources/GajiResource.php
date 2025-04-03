<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GajiResource\Pages;
use App\Filament\Resources\GajiResource\RelationManagers;
use App\Models\Gaji;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GajiResource extends Resource
{
    protected static ?string $model = Gaji::class;

    public static ?string $pluralLabel = 'Gaji';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            'index' => Pages\ListGajis::route('/'),
            'create' => Pages\CreateGaji::route('/create'),
            'edit' => Pages\EditGaji::route('/{record}/edit'),
        ];
    }
}
