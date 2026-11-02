<?php

use App\Filament\Widgets\EntretiensAVenir;
use App\Models\Vehicle;

function vehiculeEntretien($company, ?int $decalage): Vehicle
{
    return Vehicle::create([
        'company_id' => $company->id,
        'immatriculation' => 'RC-'.random_int(1000, 9999).'-GN',
        'type' => 'Camion',
        'capacite' => 5000,
        'statut_entretien' => 'ok',
        'prochain_entretien' => $decalage === null ? null : today()->addDays($decalage),
    ]);
}

it("affiche l'alerte seulement pour les entretiens de sa société", function () {
    $a = makeCompany();
    $b = makeCompany();

    vehiculeEntretien($a, -3);   // en retard
    vehiculeEntretien($a, 10);   // bientôt
    vehiculeEntretien($a, 40);   // ok
    vehiculeEntretien($a, null); // non planifié
    vehiculeEntretien($b, -1);   // autre société

    $this->actingAs(makeUser('responsable', $a));

    $widget = new EntretiensAVenir();

    expect(EntretiensAVenir::canView())->toBeTrue()
        ->and($widget->getTotal())->toBe(2)
        ->and($widget->getEnRetard())->toBe(1);
});

it("masque l'alerte quand aucun entretien n'approche", function () {
    $company = makeCompany();
    vehiculeEntretien($company, 60);

    $this->actingAs(makeUser('responsable', $company));

    expect(EntretiensAVenir::canView())->toBeFalse();
});
