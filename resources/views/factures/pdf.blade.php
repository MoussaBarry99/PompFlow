<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->numero }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 22px; margin: 0; }
        h3 { font-size: 13px; margin: 0 0 6px 0; }
        .muted { color: #666; }
        .right { text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; padding: 8px; border-bottom: 1px solid #d1d5db; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .nob td { border: none; padding: 0; }
        .total td { font-weight: bold; font-size: 14px; border-top: 2px solid #111; }
        .reste td { font-weight: bold; font-size: 14px; border-bottom: none; }
        .bloc { margin-top: 28px; }
    </style>
</head>
<body>
    @php
        $paiements = $invoice->payments()->orderBy('paye_le')->orderBy('id')->get();
        $paye = $invoice->montant_paye;
        $reste = $invoice->reste_a_payer;
    @endphp

    <table class="nob">
        <tr>
            <td><h1>{{ $company->libelle }}</h1></td>
            <td class="right">
                <div style="font-size: 20px; font-weight: bold;">FACTURE</div>
                <div>N° {{ $invoice->numero }}</div>
                <div class="muted">Émise le {{ $invoice->emise_le->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="bloc">
        <div class="muted">Facturé à</div>
        <div style="font-size: 14px; font-weight: bold;">{{ $client->nom }}</div>
    </div>

    <div class="bloc">
        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th class="right">Montant</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Transport {{ $course->adresse_depart }} → {{ $course->adresse_arrivee }}</strong><br>
                        <span class="muted">
                            Course du {{ $course->date_heure->format('d/m/Y') }}
                            @if ($course->livree_at) · livrée le {{ $course->livree_at->format('d/m/Y') }} @endif
                            @if ($course->vehicle) · véhicule {{ $course->vehicle->immatriculation }} @endif
                        </span>
                        @if ($courseClient->type_marchandise || $courseClient->poids_kg)
                            <br>
                            <span class="muted">
                                Marchandise : {{ $courseClient->type_marchandise ?? 'non précisée' }}
                                @if ($courseClient->poids_kg) · {{ (float) $courseClient->poids_kg }} kg @endif
                            </span>
                        @endif
                        @if ($courseClient->description_marchandise)
                            <br><span class="muted">{{ $courseClient->description_marchandise }}</span>
                        @endif
                    </td>
                    <td class="right">{{ \App\Support\Money::format($invoice->montant) }}</td>
                </tr>
                <tr class="total">
                    <td class="right">Total facturé</td>
                    <td class="right">{{ \App\Support\Money::format($invoice->montant) }}</td>
                </tr>
                <tr>
                    <td class="right">Montant payé</td>
                    <td class="right">{{ \App\Support\Money::format($paye) }}</td>
                </tr>
                <tr class="reste">
                    <td class="right">Reste à payer</td>
                    <td class="right">{{ \App\Support\Money::format($reste) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($paiements->isNotEmpty())
        <div class="bloc">
            <h3>Paiements reçus</h3>
            <table>
                @foreach ($paiements as $paiement)
                    <tr>
                        <td>{{ $paiement->paye_le->format('d/m/Y') }} · {{ $paiement->mode_libelle }}@if ($paiement->note) · {{ $paiement->note }}@endif</td>
                        <td class="right">{{ \App\Support\Money::format($paiement->montant) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    @if ($reste === 0)
        <p class="bloc" style="color: #15803d; font-size: 16px; font-weight: bold;">FACTURE ACQUITTÉE</p>
    @endif

    <p class="muted bloc">
        Facture établie par {{ $company->libelle }} · Montants exprimés en francs guinéens (GNF)
        · État des paiements au {{ now()->format('d/m/Y') }}.
    </p>
</body>
</html>
