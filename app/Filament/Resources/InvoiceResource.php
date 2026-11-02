<?php

namespace App\Filament\Resources;

use App\Filament\Actions\EncaisserPaiementAction;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Support\Money;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Opérations';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'facture';

    protected static ?string $pluralModelLabel = 'Factures';

    protected static ?string $navigationLabel = 'Factures et paiements';

    public static function table(Table $table): Table
    {
        $sommePayee = '(select coalesce(sum(montant), 0) from invoice_payments where invoice_payments.invoice_id = invoices.id)';

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N°')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('courseClient.client.nom')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('emise_le')
                    ->label('Émise le')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('montant')
                    ->label('Facturé')
                    ->formatStateUsing(fn ($state) => Money::format($state)),
                Tables\Columns\TextColumn::make('montant_paye')
                    ->label('Payé')
                    ->formatStateUsing(fn ($state) => Money::format($state)),
                Tables\Columns\TextColumn::make('reste_a_payer')
                    ->label('Reste à payer')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('statut_paiement')
                    ->label('Paiement')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'payee' => 'Payée',
                        'partielle' => 'Partielle',
                        default => 'Impayée',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'payee' => 'success',
                        'partielle' => 'warning',
                        default => 'danger',
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('paiement')
                    ->label('Paiement')
                    ->options([
                        'impayee' => 'Impayées',
                        'partielle' => 'Partielles',
                        'payee' => 'Payées',
                    ])
                    ->query(function (Builder $query, array $data) use ($sommePayee) {
                        return match ($data['value'] ?? null) {
                            'impayee' => $query->whereRaw("$sommePayee = 0"),
                            'partielle' => $query->whereRaw("$sommePayee > 0")->whereRaw("$sommePayee < invoices.montant"),
                            'payee' => $query->whereRaw("$sommePayee >= invoices.montant"),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                EncaisserPaiementAction::make(),
                Tables\Actions\Action::make('historique')
                    ->label('Paiements')
                    ->icon('heroicon-o-list-bullet')
                    ->color('gray')
                    ->modalHeading(fn (Invoice $record) => 'Paiements — '.$record->numero)
                    ->modalContent(fn (Invoice $record) => view('filament.invoice-payments', ['invoice' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer'),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (Invoice $record) => route('factures.telecharger', $record->course_client_id)),
            ])
            ->emptyStateHeading('Aucune facture')
            ->emptyStateDescription('Les factures apparaissent ici une fois émises depuis une course livrée.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
        ];
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
            ->where('company_id', auth()->user()->company_id)
            ->with(['courseClient.client', 'payments']);
    }
}
