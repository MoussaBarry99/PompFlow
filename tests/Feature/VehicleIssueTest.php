<?php

use App\Filament\Resources\VehicleIssueResource;
use App\Models\Vehicle;
use App\Models\VehicleIssue;

function makeVehicleFor($company, $chauffeur = null): Vehicle
{
    return Vehicle::create([
        'company_id' => $company->id,
        'driver_id' => $chauffeur?->id,
        'immatriculation' => 'RC-'.random_int(1000, 9999).'-GN',
        'type' => 'Camion',
        'capacite' => 5000,
        'statut_entretien' => 'ok',
    ]);
}

it('permet au chauffeur de signaler un problème sur son véhicule', function () {
    $company = makeCompany();
    $chauffeur = makeUser('chauffeur', $company);
    $vehicle = makeVehicleFor($company, $chauffeur);

    $this->actingAs($chauffeur)
        ->post(route('chauffeur.problemes.store'), [
            'type' => 'freins',
            'gravite' => 'urgente',
            'description' => 'Les freins grincent fortement.',
        ])
        ->assertRedirect(route('chauffeur.problemes.create'));

    $this->assertDatabaseHas('vehicle_issues', [
        'company_id' => $company->id,
        'vehicle_id' => $vehicle->id,
        'user_id' => $chauffeur->id,
        'type' => 'freins',
        'gravite' => 'urgente',
        'statut' => 'ouvert',
    ]);
});

it('rejette un signalement invalide', function () {
    $company = makeCompany();
    $chauffeur = makeUser('chauffeur', $company);
    makeVehicleFor($company, $chauffeur);

    $this->actingAs($chauffeur)
        ->post(route('chauffeur.problemes.store'), [
            'type' => 'nimporte-quoi',
            'gravite' => 'urgente',
            'description' => 'ok',
        ])
        ->assertSessionHasErrors(['type', 'description']);

    expect(VehicleIssue::count())->toBe(0);
});

it("refuse le signalement d'un chauffeur sans véhicule", function () {
    $company = makeCompany();
    $chauffeur = makeUser('chauffeur', $company);

    $this->actingAs($chauffeur)
        ->post(route('chauffeur.problemes.store'), [
            'type' => 'panne',
            'gravite' => 'faible',
            'description' => 'Un souci quelconque.',
        ])
        ->assertSessionHasErrors();

    expect(VehicleIssue::count())->toBe(0);
});

it('ne montre au responsable que les problèmes de sa société', function () {
    $companyA = makeCompany();
    $companyB = makeCompany();

    $chauffeurA = makeUser('chauffeur', $companyA);
    $chauffeurB = makeUser('chauffeur', $companyB);
    $vehicleA = makeVehicleFor($companyA, $chauffeurA);
    $vehicleB = makeVehicleFor($companyB, $chauffeurB);

    $issueA = VehicleIssue::create([
        'company_id' => $companyA->id, 'vehicle_id' => $vehicleA->id, 'user_id' => $chauffeurA->id,
        'type' => 'panne', 'gravite' => 'moyenne', 'description' => 'Panne A',
    ]);
    VehicleIssue::create([
        'company_id' => $companyB->id, 'vehicle_id' => $vehicleB->id, 'user_id' => $chauffeurB->id,
        'type' => 'panne', 'gravite' => 'moyenne', 'description' => 'Panne B',
    ]);

    $this->actingAs(makeUser('responsable', $companyA));

    expect(VehicleIssueResource::getEloquentQuery()->pluck('id')->all())->toBe([$issueA->id]);
});