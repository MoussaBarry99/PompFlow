<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    protected static ?string $title = 'Dépenses déclarées';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('montant')
                    ->label('Montant')
                    ->money('eur')
                    ->summarize(Sum::make()->label('Total')->money('eur')),
                Tables\Columns\TextColumn::make('description')
                    ->label('Détail')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Déclarée par'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}