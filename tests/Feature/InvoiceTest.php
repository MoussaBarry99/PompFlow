<?php

use App\Models\Client;
use App\Models\Course;
use App\Models\CourseClient;
use App\Models\Invoice;
use App\Models\Vehicle;

function courseClientDe($company, string $statut = 'livree', int $montant = 1500000): CourseClient
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
        'statut' => $statut,
        'livree_at' => $statut === 'livree' ? now()->subHours(3) : null,
    ]);

    return CourseClient::forceCreate([
        'course_id' => $course->id,
        'client_id' => $client->id,
        'montant_facture' => $montant,
    ]);
}

it('numérote les factures par société, sans trou', function () {
    $a = makeCompany();
    $b = makeCompany();
    $annee = now()->year;

    $premiere = Invoice::emettre(courseClientDe($a));
    $deuxieme = Invoice::emettre(courseClientDe($a));
    $autreSociete = Invoice::emettre(courseClientDe($b));

    expect($premiere->numero)->toBe(sprintf('FAC-%d-0001', $annee))
        ->and($deuxieme->numero)->toBe(sprintf('FAC-%d-0002', $annee))
        ->and($autreSociete->numero)->toBe(sprintf('FAC-%d-0001', $annee));
});

it('ne crée pas deux factures pour le même client et la même course', function () {
    $courseClient = courseClientDe(makeCompany());

    $premiere = Invoice::emettre($courseClient);
    $seconde = Invoice::emettre($courseClient);

    expect($seconde->id)->toBe($premiere->id)
        ->and(Invoice::count())->toBe(1);
});

it('fait télécharger la facture PDF au responsable de la société', function () {
    $company = makeCompany();
    $courseClient = courseClientDe($company);

    $this->actingAs(makeUser('responsable', $company))
        ->get(route('factures.telecharger', $courseClient))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect(Invoice::count())->toBe(1);
});

it("refuse la facture d'une autre société", function () {
    $courseClient = courseClientDe(makeCompany());

    $this->actingAs(makeUser('responsable', makeCompany()))
        ->get(route('factures.telecharger', $courseClient))
        ->assertNotFound();
});

it("refuse la facture tant que la course n'est pas livrée", function () {
    $company = makeCompany();
    $courseClient = courseClientDe($company, 'planifiee');

    $this->actingAs(makeUser('responsable', $company))
        ->get(route('factures.telecharger', $courseClient))
        ->assertForbidden();

    expect(Invoice::count())->toBe(0);
});

it('interdit les factures au chauffeur', function () {
    $company = makeCompany();
    $courseClient = courseClientDe($company);

    $this->actingAs(makeUser('chauffeur', $company))
        ->get(route('factures.telecharger', $courseClient))
        ->assertForbidden();
});
