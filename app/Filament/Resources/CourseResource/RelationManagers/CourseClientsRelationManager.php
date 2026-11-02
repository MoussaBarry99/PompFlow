<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Filament\Actions\EncaisserPaiementAction;
use App\Models\CourseClient;
use App\Support\Money;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseClientsRelationManager extends RelationManager
{
    protected static string $relationship = 'courseClients';

    protected static ?string $title = 'Clients, factures et paiements';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->description("La facture et les paiements d'un client sont disponibles une fois la course livrée.")
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['client', 'course', 'invoice.payments']))
            ->columns([
                Tables\Columns\TextColumn::make('client.nom')
                    ->label('Client'),
                Tables\Columns\TextColumn::make('montant_facture')
                    ->label('Facturé')
                    ->formatStateUsing(fn ($state) => Money::format($state)),
                Tables\Columns\TextColumn::make('paye')
                    ->label('Payé')
                    ->state(fn (CourseClient $record) => $record->invoice?->montant_paye)
                    ->formatStateUsing(fn ($state) => $state === null ? '—' : Money::format($state)),
                Tables\Columns\TextColumn::make('reste')
                    ->label('Reste à payer')
                    ->state(fn (CourseClient $record) => $record->invoice?->reste_a_payer)
                    ->formatStateUsing(fn ($state) => $state === null ? '—' : Money::format($state))
                    ->color(fn ($state) => $state === null ? 'gray' : ($state > 0 ? 'danger' : 'success')),
                Tables\Columns\TextColumn::make('invoice.numero')
                    ->label('Facture')
                    ->badge()
                    ->placeholder('Non émise')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->actions([
                EncaisserPaiementAction::make(),
                Tables\Actions\Action::make('facture')
                    ->label('Facture PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (CourseClient $record) => route('factures.telecharger', $record))
                    ->visible(fn () => $this->getOwnerRecord()->statut === 'livree'),
            ])
            ->paginated(false);
    }
}
