<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public bool $reopening = false;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'cree_par_user_id',
        'adresse_depart',
        'adresse_arrivee',
        'date_heure',
        'statut',
        'livraison_prevue_at',
        'livree_at',
    ];

    protected $casts = [
        'date_heure' => 'datetime',
        'livraison_prevue_at' => 'datetime',
        'livree_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Course $course) {
            if ($course->isDirty('statut') && $course->statut === 'livree' && ! $course->livree_at) {
                $course->livree_at = now();
            }
        });

        static::updating(function (Course $course) {
            if ($course->getOriginal('statut') === 'livree' && ! $course->reopening) {
                throw new \RuntimeException('Une course livrée ne peut plus être modifiée.');
            }
        });

        static::deleting(function (Course $course) {
            if ($course->statut === 'livree') {
                throw new \RuntimeException('Une course livrée ne peut pas être supprimée.');
            }
        });

        static::created(function (Course $course) {
            $course->statusHistories()->create(['statut' => $course->statut]);
        });

        static::updated(function (Course $course) {
            if ($course->wasChanged('statut')) {
                $course->statusHistories()->create(['statut' => $course->statut]);
            }
        });
    }

    public function rouvrir(): void
    {
        if ($this->statut !== 'livree') {
            throw new \RuntimeException('Seule une course livrée peut être rouverte.');
        }

        $this->reopening = true;
        $this->statut = 'en_cours';
        $this->livree_at = null;
        $this->save();
        $this->reopening = false;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function courseClients()
    {
        return $this->hasMany(CourseClient::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'course_clients');
    }

    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class);
    }

    public function expenses()
    {
        return $this->hasMany(CourseExpense::class);
    }

    public function getMontantFactureTotalAttribute(): float
    {
        $sum = array_key_exists('course_clients_sum_montant_facture', $this->attributes)
            ? $this->attributes['course_clients_sum_montant_facture']
            : $this->courseClients()->sum('montant_facture');

        return (float) $sum;
    }

    public function getTotalDepensesAttribute(): float
    {
        $sum = array_key_exists('expenses_sum_montant', $this->attributes)
            ? $this->attributes['expenses_sum_montant']
            : $this->expenses()->sum('montant');

        return (float) $sum;
    }

    public function getMontantNetAttribute(): float
    {
        return $this->montant_facture_total - $this->total_depenses;
    }

    public function getEnRetardAttribute(): bool
    {
        if (! $this->livraison_prevue_at || $this->statut === 'annulee') {
            return false;
        }

        return ($this->livree_at ?? now())->greaterThan($this->livraison_prevue_at);
    }

    public function getStatutAfficheAttribute(): string
    {
        return match (true) {
            $this->statut === 'livree' && $this->en_retard => 'Livrée en retard',
            $this->statut === 'livree' => 'Livrée',
            $this->statut === 'annulee' => 'Annulée',
            $this->en_retard => 'En retard',
            $this->statut === 'en_cours' => 'En cours',
            default => 'Planifiée',
        };
    }

    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut_affiche) {
            'Livrée' => 'success',
            'Livrée en retard' => 'warning',
            'En retard' => 'danger',
            'En cours' => 'info',
            default => 'gray',
        };
    }
}