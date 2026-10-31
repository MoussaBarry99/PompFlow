<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->numero }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 22px; margin: 0; }
        .muted { color: #666; }
        .right { text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; padding: 8px; border-bottom: 1px solid #d1d5db; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .nob td { border: none; padding: 0; }
        .total td { font-weight: bold; font-size: 14px; border-top: 2px solid #111; border-bottom: none; }
        .bloc { margin-top: 28px; }
    </style>
</head>
<body>
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
                    <td class="right">Total à payer</td>
                    <td class="right">{{ \App\Support\Money::format($invoice->montant) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p class="muted bloc">
        Facture établie par {{ $company->libelle }} · Montants exprimés en francs guinéens (GNF).
    </p>
</body>
</html>
