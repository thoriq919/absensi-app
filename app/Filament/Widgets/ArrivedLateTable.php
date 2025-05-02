<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ArrivedLateTable extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
               
            )
            ->columns([
                // ...
            ]);
    }
}
