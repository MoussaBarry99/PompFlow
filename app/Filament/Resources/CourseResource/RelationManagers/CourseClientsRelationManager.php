<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Models\CourseClient;
use App\Support\Money;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseClientsRelationManager extends RelationManager
{
    protected static string $relationship = 'courseClients';

    protected static ?string $title = 'Clients et factures';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->description("La facture d'un client est disponible une fois la course livrée.")
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['client', 'invoice']))
            ->columns([
                Tables\Columns\TextColumn::make('client.nom')
                    ->label('Client'),
                Tables\Columns\TextColumn::make('type_marchandise')
                    ->label('Marchandise')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('montant_facture')
                    ->label('Montant facturé')
                    ->formatStateUsing(fn ($state) => Money::format($state)),
                Tables\Columns\TextColumn::make('invoice.numero')
                    ->label('Facture')
                    ->badge()
                    ->placeholder('Non émise')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->actions([
                Tables\Actions\Action::make('facture')
                    ->label('Facture PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (CourseClient $record) => route('factures.telecharger', $record))
                    ->visible(fn () => $this->getOwnerRecord()->statut === 'livree'),
            ])
            ->paginated(false);
    }
}
