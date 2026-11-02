<?php

namespace App\Filament\Actions;

use App\Models\CourseClient;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Support\Money;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use InvalidArgumentException;

/**
 * Enregistre un paiement. Utilisable sur une facture (Invoice)
 * ou directement sur la ligne client d'une course (CourseClient) : dans ce cas
 * la facture est émise automatiquement si elle ne l'est pas encore.
 */
class EncaisserPaiementAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'encaisser';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Encaisser un paiement')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->modalHeading('Enregistrer un paiement')
            ->modalDescription(function ($record): string {
                $facture = $record instanceof CourseClient ? $record->invoice : $record;
                $reste = $facture ? $facture->reste_a_payer : (int) $record->montant_facture;

                return 'Reste à payer : '.Money::format($reste);
            })
            ->modalSubmitActionLabel('Enregistrer')
            ->visible(function ($record): bool {
                if ($record instanceof CourseClient) {
                    // Encaissable dès la planification (acompte), sauf course annulée.
                    return $record->course->statut !== 'annulee'
                        && (int) $record->montant_facture > 0
                        && (! $record->invoice || $record->invoice->reste_a_payer > 0);
                }

                return $record->reste_a_payer > 0;
            })
            ->form([
                Forms\Components\TextInput::make('montant')
                    ->label('Montant reçu')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->minValue(1)
                    ->suffix('GNF')
                    ->placeholder('Montant réellement reçu')
                    ->autofocus(),
                Forms\Components\DatePicker::make('paye_le')
                    ->label('Date du paiement')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->default(today())
                    ->closeOnDateSelection()
                    ->required()
                    ->maxDate(today()->endOfDay()),
                Forms\Components\Select::make('mode')
                    ->label('Mode de paiement')
                    ->options(InvoicePayment::MODES)
                    ->default('especes')
                    ->selectablePlaceholder(false)
                    ->required(),
                Forms\Components\TextInput::make('note')
                    ->label('Note (optionnelle)')
                    ->maxLength(255),
            ])
            ->action(function ($record, array $data, Action $action): void {
                $facture = $record instanceof CourseClient ? Invoice::emettre($record) : $record;

                try {
                    $facture->enregistrerPaiement(
                        (int) $data['montant'],
                        $data['mode'],
                        $data['note'] ?? null,
                        Carbon::parse($data['paye_le']),
                        auth()->id(),
                    );
                } catch (InvalidArgumentException $e) {
                    Notification::make()->danger()->title($e->getMessage())->send();
                    $action->halt();

                    return;
                }

                Notification::make()->success()->title('Paiement enregistré')->send();
            });
    }
}
