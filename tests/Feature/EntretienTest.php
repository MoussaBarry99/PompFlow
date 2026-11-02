<?php

use App\Models\Vehicle;

it("classe l'état du prochain entretien", function (?int $decalage, string $attendu) {
    $vehicle = new Vehicle();
    $vehicle->prochain_entretien = $decalage === null ? null : today()->addDays($decalage);

    expect($vehicle->entretien_statut)->toBe($attendu);
})->with([
    'non planifié' => [null, 'non_planifie'],
    'dépassé' => [-1, 'en_retard'],
    "aujourd'hui" => [0, 'bientot'],
    'dans 14 jours' => [14, 'bientot'],
    'dans 15 jours' => [15, 'ok'],
]);