<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CourseResource;
use App\Models\Course;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PlanningDuJour extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->role === 'responsable';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Planning du jour')
            ->query(
                Course::query()
                    ->where('company_id', auth()->user()->company_id)
                    ->whereDate('date_heure', today())
                    ->where('statut', '!=', 'annulee')
                    ->with(['clients', 'vehicle.driver'])
            )
            ->defaultSort('date_heure')
            ->columns([
                Tables\Columns\TextColumn::make('date_heure')
                    ->label('Départ')
                    ->dateTime('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clients.nom')
                    ->label('Clients')
                    ->badge(),
                Tables\Columns\TextColumn::make('adresse_depart')
                    ->label('De'),
                Tables\Columns\TextColumn::make('adresse_arrivee')
                    ->label('Vers'),
                Tables\Columns\TextColumn::make('vehicle.immatriculation')
                    ->label('Véhicule')
                    ->placeholder('Non affecté'),
                Tables\Columns\TextColumn::make('vehicle.driver.name')
                    ->label('Chauffeur')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('statut_affiche')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (Course $record): string => $record->statut_couleur),
            ])
            ->recordUrl(fn (Course $record): string => CourseResource::getUrl('view', ['record' => $record]))
            ->paginated(false)
            ->emptyStateHeading('Aucune course prévue ce jour');
    }
}