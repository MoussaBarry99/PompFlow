<?php

namespace App\Models;

use App\Support\Money;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Invoice extends Model
{
    protected $fillable = ['company_id', 'course_client_id', 'numero', 'emise_le', 'montant'];

    protected function casts(): array
    {
        return ['emise_le' => 'date'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function courseClient(): BelongsTo
    {
        return $this->belongsTo(CourseClient::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    /** Montant déjà encaissé (somme des paiements). */
    public function getMontantPayeAttribute(): int
    {
        $total = $this->relationLoaded('payments')
            ? $this->payments->sum('montant')
            : $this->payments()->sum('montant');

        return (int) $total;
    }

    public function getResteAPayerAttribute(): int
    {
        return max(0, (int) $this->montant - $this->montant_paye);
    }

    /** impayee | partielle | payee */
    public function getStatutPaiementAttribute(): string
    {
        return match (true) {
            $this->montant_paye >= (int) $this->montant => 'payee',
            $this->montant_paye > 0 => 'partielle',
            default => 'impayee',
        };
    }

    /**
     * Enregistre un paiement, sans jamais dépasser le reste à payer.
     * La facture est verrouillée pendant l'opération : deux encaissements
     * simultanés ne peuvent pas faire dépasser le total.
     */
    public function enregistrerPaiement(
        int $montant,
        string $mode = 'especes',
        ?string $note = null,
        ?CarbonInterface $payeLe = null,
        ?int $userId = null,
    ): InvoicePayment {
        return DB::transaction(function () use ($montant, $mode, $note, $payeLe, $userId) {
            $facture = static::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if ($montant < 1) {
                throw new InvalidArgumentException('Le montant doit être supérieur à 0.');
            }

            if ($montant > $facture->reste_a_payer) {
                throw new InvalidArgumentException(
                    'Le montant dépasse le reste à payer ('.Money::format($facture->reste_a_payer).').'
                );
            }

            return $facture->payments()->create([
                'company_id' => $facture->company_id,
                'user_id' => $userId,
                'montant' => $montant,
                'paye_le' => ($payeLe ?? today())->toDateString(),
                'mode' => $mode,
                'note' => $note,
            ]);
        });
    }

    /**
     * Émet la facture d'un client pour une course, ou renvoie celle déjà émise.
     * Numérotation continue par société et par année : FAC-2026-0001, FAC-2026-0002…
     * Le montant est figé à l'émission.
     */
    public static function emettre(CourseClient $courseClient): self
    {
        return DB::transaction(function () use ($courseClient) {
            $existante = static::where('course_client_id', $courseClient->id)->first();

            if ($existante) {
                return $existante;
            }

            $companyId = $courseClient->course->company_id;
            $annee = now()->year;

            $rang = static::where('company_id', $companyId)
                ->whereYear('emise_le', $annee)
                ->lockForUpdate()
                ->count() + 1;

            return static::create([
                'company_id' => $companyId,
                'course_client_id' => $courseClient->id,
                'numero' => sprintf('FAC-%d-%04d', $annee, $rang),
                'emise_le' => today(),
                'montant' => $courseClient->montant_facture,
            ]);
        });
    }
}
