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

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->role === 'responsable';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Planning du jour — '.now()->translatedFormat('l j F'))
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
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('livraison_prevue_at')
                    ->label('Arrivée prévue')
                    ->dateTime('H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('clients.nom')
                    ->label('Clients')
                    ->badge(),
                Tables\Columns\TextColumn::make('adresse_depart')
                    ->label('De')
                    ->limit(25)
                    ->tooltip(fn (Course $record) => $record->adresse_depart),
                Tables\Columns\TextColumn::make('adresse_arrivee')
                    ->label('Vers')
                    ->limit(25)
                    ->tooltip(fn (Course $record) => $record->adresse_arrivee),
                Tables\Columns\TextColumn::make('vehicle.immatriculation')
                    ->label('Véhicule')
                    ->placeholder('Non affecté')
                    ->color(fn (Course $record) => $record->vehicle ? null : 'danger'),
                Tables\Columns\TextColumn::make('vehicle.driver.name')
                    ->label('Chauffeur')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('statut_affiche')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (Course $record): string => $record->statut_couleur),
            ])
            ->recordClasses(fn (Course $record) => $record->en_retard
                ? 'bg-danger-50 dark:bg-danger-950/20'
                : null)
            ->recordUrl(fn (Course $record): string => CourseResource::getUrl('view', ['record' => $record]))
            ->paginated(false)
            ->emptyStateHeading('Aucune course prévue ce jour')
            ->emptyStateDescription('Les courses planifiées pour aujourd\'hui apparaîtront ici.');
    }
}