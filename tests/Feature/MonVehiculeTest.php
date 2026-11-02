<?php

use App\Models\Vehicle;

it('affiche la fiche du véhicule au chauffeur', function () {
    $company = makeCompany();
    $chauffeur = makeUser('chauffeur', $company);
    $date = today()->addDays(5);

    Vehicle::create([
        'company_id' => $company->id,
        'driver_id' => $chauffeur->id,
        'immatriculation' => 'RC-1234-GN',
        'type' => 'Camion',
        'capacite' => 5000,
        'statut_entretien' => 'ok',
        'prochain_entretien' => $date,
    ]);

    $this->actingAs($chauffeur)
        ->get(route('chauffeur.vehicule'))
        ->assertOk()
        ->assertSee('RC-1234-GN')
        ->assertSee($date->format('d/m/Y'));
});

it("indique l'absence de véhicule", function () {
    $company = makeCompany();

    $this->actingAs(makeUser('chauffeur', $company))
        ->get(route('chauffeur.vehicule'))
        ->assertOk()
        ->assertSee('Aucun véhicule');
});

it("refuse l'accès au responsable", function () {
    $company = makeCompany();

    $this->actingAs(makeUser('responsable', $company))
        ->get(route('chauffeur.vehicule'))
        ->assertForbidden();
});