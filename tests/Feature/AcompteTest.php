<?php

use App\Models\Client;
use App\Models\Course;
use App\Models\CourseClient;
use App\Models\Invoice;
use App\Models\Vehicle;

it("permet d'encaisser une course planifiée avant sa livraison", function () {
    $company = makeCompany();

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
        'date_heure' => now()->addDay(),
        'livraison_prevue_at' => now()->addDays(2),
        'statut' => 'planifiee',
    ]);
    $courseClient = CourseClient::forceCreate([
        'course_id' => $course->id,
        'client_id' => $client->id,
        'montant_facture' => 2000000,
    ]);

    $facture = Invoice::emettre($courseClient);
    $facture->enregistrerPaiement(800000, 'especes');
    $facture = $facture->fresh();

    expect($facture->statut_paiement)->toBe('partielle')
        ->and($facture->reste_a_payer)->toBe(1200000)
        ->and($course->fresh()->statut)->toBe('planifiee');

    $facture->enregistrerPaiement(1200000, 'virement');

    expect($facture->fresh()->statut_paiement)->toBe('payee');
});
