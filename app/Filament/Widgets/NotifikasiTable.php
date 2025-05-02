<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotifikasiTable extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->heading('Aktivitas Terbaru')
            ->description('Daftar aktivitas yang belum terbaca.')
            ->query(
                DatabaseNotification::query()
                    ->where('notifiable_id', Auth::id())
                    ->whereNull('read_at')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('data.title') 
                    ->label('Aktivitas')
                    ->wrap(), 
                TextColumn::make('created_at')
                    ->since()
                    ->label('Waktu'),
            ])
            ->paginated(false)
            ->deferLoading();
    }
}
