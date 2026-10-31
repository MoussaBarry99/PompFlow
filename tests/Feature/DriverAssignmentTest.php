<?php

it("affecte un véhicule à un chauffeur sans voler celui d'un autre ni sortir de la société", function () {
    $a = makeCompany('A');
    $b = makeCompany('B');

    $d1 = makeUser('chauffeur', $a);
    $d2 = makeUser('chauffeur', $a);

    $v1 = makeVehicle($a, $d1);
    $v2 = makeVehicle($a);
    $v3 = makeVehicle($a);
    $vb = makeVehicle($b);

    expect($d2->affecterVehicule($v1->id))->toBeFalse()
        ->and($d2->affecterVehicule($vb->id))->toBeFalse()
        ->and($v1->fresh()->driver_id)->toBe($d1->id);

    expect($d2->affecterVehicule($v2->id))->toBeTrue()
        ->and($v2->fresh()->driver_id)->toBe($d2->id);

    // Changer de véhicule libère l'ancien.
    expect($d2->affecterVehicule($v3->id))->toBeTrue()
        ->and($v2->fresh()->driver_id)->toBeNull()
        ->and($v3->fresh()->driver_id)->toBe($d2->id);

    // Retirer l'affectation.
    expect($d2->affecterVehicule(null))->toBeTrue()
        ->and($v3->fresh()->driver_id)->toBeNull();
});