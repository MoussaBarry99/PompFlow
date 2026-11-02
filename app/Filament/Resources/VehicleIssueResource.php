<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleIssueResource\Pages;
use App\Models\VehicleIssue;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VehicleIssueResource extends Resource
{
    protected static ?string $model = VehicleIssue::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $modelLabel = 'problème véhicule';

    protected static ?string $pluralModelLabel = 'Problèmes véhicules';

    protected static ?string $navigationLabel = 'Problèmes véhicules';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Signalé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.immatriculation')
                    ->label('Véhicule')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Signalé par'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state) => VehicleIssue::TYPES[$state] ?? $state),
                Tables\Columns\TextColumn::make('gravite')
                    ->label('Gravité')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => VehicleIssue::GRAVITES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'urgente' => 'danger',
                        'moyenne' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(60)
                    ->tooltip(fn (VehicleIssue $record) => $record->description)
                    ->wrap(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'resolu' ? 'Résolu' : 'Ouvert')
                    ->color(fn (string $state): string => $state === 'resolu' ? 'success' : 'danger'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(['ouvert' => 'Ouvert', 'resolu' => 'Résolu'])
                    ->default('ouvert'),
            ])
            ->actions([
                Tables\Actions\Action::make('resoudre')
                    ->label('Marquer comme résolu')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (VehicleIssue $record): bool => $record->statut === 'ouvert')
                    ->action(fn (VehicleIssue $record) => $record->update([
                        'statut' => 'resolu',
                        'resolu_at' => now(),
                    ])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicleIssues::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $ouverts = static::getEloquentQuery()->where('statut', 'ouvert')->count();

        return $ouverts > 0 ? (string) $ouverts : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'responsable';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('company_id', auth()->user()->company_id);
    }
}
