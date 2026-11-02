<?php

use App\Filament\Resources\InvoiceResource;
use App\Models\Client;
use App\Models\Course;
use App\Models\CourseClient;
use App\Models\Invoice;
use App\Models\Vehicle;

function factureLivree($company, int $montant = 1500000): Invoice
{
    $vehicle = Vehicle::create([
        'company_id' => $company->id,
        'immatriculation' => 'RC-'.random_int(1000, 9999).'-GN',
        'type' => 'Camion',
        'capacite' => 5000,
        'statut_entretien' => 'ok',
    ]);

    $client = Client::forceCreate(['company_id' => $company->id, 'nom' => 'Client Test']);

    $course = Course::forceCreate([
        'company_id' => $company->id,
        'vehicle_id' => $vehicle->id,
        'cree_par_user_id' => makeUser('responsable', $company)->id,
        'adresse_depart' => 'Conakry',
        'adresse_arrivee' => 'Kindia',
        'date_heure' => now()->subHours(5),
        'livraison_prevue_at' => now()->subHours(2),
        'statut' => 'livree',
        'livree_at' => now()->subHours(3),
    ]);

    $courseClient = CourseClient::forceCreate([
        'course_id' => $course->id,
        'client_id' => $client->id,
        'montant_facture' => $montant,
    ]);

    return Invoice::emettre($courseClient);
}

it('calcule le payé, le reste et le statut au fil des paiements partiels', function () {
    $facture = factureLivree(makeCompany(), 1500000);

    expect($facture->statut_paiement)->toBe('impayee')
        ->and($facture->reste_a_payer)->toBe(1500000);

    $facture->enregistrerPaiement(500000, 'especes');
    $facture = $facture->fresh();

    expect($facture->montant_paye)->toBe(500000)
        ->and($facture->reste_a_payer)->toBe(1000000)
        ->and($facture->statut_paiement)->toBe('partielle');

    $facture->enregistrerPaiement(1000000, 'mobile_money');
    $facture = $facture->fresh();

    expect($facture->montant_paye)->toBe(1500000)
        ->and($facture->reste_a_payer)->toBe(0)
        ->and($facture->statut_paiement)->toBe('payee')
        ->and($facture->payments)->toHaveCount(2);
});

it('refuse un paiement supérieur au reste à payer', function () {
    $facture = factureLivree(makeCompany(), 1000000);
    $facture->enregistrerPaiement(400000);

    expect(fn () => $facture->fresh()->enregistrerPaiement(700000))
        ->toThrow(InvalidArgumentException::class);

    expect($facture->fresh()->montant_paye)->toBe(400000);
});

it('refuse un paiement nul ou négatif', function () {
    $facture = factureLivree(makeCompany());

    expect(fn () => $facture->enregistrerPaiement(0))->toThrow(InvalidArgumentException::class);
    expect(fn () => $facture->enregistrerPaiement(-5000))->toThrow(InvalidArgumentException::class);
});

it('ne montre au responsable que les factures de sa société', function () {
    $societeA = makeCompany();
    $societeB = makeCompany();

    $factureA = factureLivree($societeA);
    factureLivree($societeB);

    $this->actingAs(makeUser('responsable', $societeA));

    expect(InvoiceResource::getEloquentQuery()->pluck('id')->all())->toBe([$factureA->id]);
});
