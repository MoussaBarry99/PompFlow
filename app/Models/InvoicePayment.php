<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    public const MODES = [
        'especes' => 'Espèces',
        'virement' => 'Virement bancaire',
        'mobile_money' => ('Orange Money / Mobile Money'),
        'cheque' => 'Chèque',
        'autre' => 'Autre',
    ];

    protected $fillable = ['company_id', 'invoice_id', 'user_id', 'montant', 'paye_le', 'mode', 'note'];

    protected function casts(): array
    {
        return ['paye_le' => 'date'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getModeLibelleAttribute(): string
    {
        return self::MODES[$this->mode] ?? ucfirst($this->mode);
    }
}
