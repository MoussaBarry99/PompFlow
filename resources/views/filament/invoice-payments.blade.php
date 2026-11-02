@php
    use App\Support\Money;

    $paiements = $invoice->payments->sortByDesc('paye_le');
@endphp

<div style="display: flex; flex-direction: column; gap: 1rem;">
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; text-align: center;">
        <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Facturé</div>
            <div class="font-semibold">{{ Money::format($invoice->montant) }}</div>
        </div>
        <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Payé</div>
            <div class="font-semibold">{{ Money::format($invoice->montant_paye) }}</div>
        </div>
        <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Reste à payer</div>
            <div class="font-semibold" style="color: {{ $invoice->reste_a_payer > 0 ? '#dc2626' : '#16a34a' }};">
                {{ Money::format($invoice->reste_a_payer) }}
            </div>
        </div>
    </div>

    <div>
        @forelse ($paiements as $paiement)
            <div class="text-sm" style="display: flex; justify-content: space-between; gap: 1rem; padding: 0.5rem 0; border-top: 1px solid rgba(128,128,128,0.25);">
                <div>
                    <div class="font-medium">{{ $paiement->paye_le->format('d/m/Y') }} · {{ $paiement->mode_libelle }}</div>
                    @if ($paiement->note)
                        <div class="text-gray-500 dark:text-gray-400">{{ $paiement->note }}</div>
                    @endif
                    @if ($paiement->user)
                        <div class="text-gray-500 dark:text-gray-400">Enregistré par {{ $paiement->user->name }}</div>
                    @endif
                </div>
                <div class="font-semibold">{{ Money::format($paiement->montant) }}</div>
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">Aucun paiement enregistré.</p>
        @endforelse
    </div>
</div>
