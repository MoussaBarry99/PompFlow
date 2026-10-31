<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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
