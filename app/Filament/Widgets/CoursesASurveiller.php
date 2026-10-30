<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CourseResource;
use App\Models\Course;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CoursesASurveiller extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->role === 'responsable';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Courses à surveiller')
            ->description('Courses en cours ou en retard de livraison')
            ->query(
                Course::query()
                    ->where('company_id', auth()->user()->company_id)
                    ->whereNotIn('statut', ['livree', 'annulee'])
                    ->where(function ($query) {
                        $query->where('statut', 'en_cours')
                            ->orWhere('livraison_prevue_at', '<', now());
                    })
                    ->with(['clients', 'vehicle.driver'])
            )
            ->defaultSort('livraison_prevue_at')
            ->columns([
                Tables\Columns\TextColumn::make('livraison_prevue_at')
                    ->label('Livraison prévue')
                    ->dateTime('d/m H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clients.nom')
                    ->label('Clients')
                    ->badge(),
                Tables\Columns\TextColumn::make('adresse_arrivee')
                    ->label('Destination'),
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
            ->emptyStateHeading('Rien à signaler')
            ->emptyStateDescription('Aucune course en cours ni en retard.');
    }
}