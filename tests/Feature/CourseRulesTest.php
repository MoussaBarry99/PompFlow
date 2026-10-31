<?php

use App\Models\Course;

beforeEach(function () {
    $this->withoutVite();

    $this->company = makeCompany();
    $this->chef = makeUser('responsable', $this->company);
    $this->driver = makeUser('chauffeur', $this->company);
    $this->vehicle = makeVehicle($this->company, $this->driver);

    // Demain de 8 h à 16 h sur $this->vehicle, facturée 1500000 GNF.
    $this->course = makeCourse($this->company, $this->vehicle, [], 1500000);
});

it('enregistre les dépenses du chauffeur et calcule le net', function () {
    $this->actingAs($this->driver);

    $this->post(route('chauffeur.courses.expenses.store', $this->course->id), [
        'type' => 'carburant',
        'montant' => 85000,
        'description' => 'Plein',
    ])->assertRedirect();

    $this->post(route('chauffeur.courses.expenses.store', $this->course->id), [
        'type' => 'peage',
        'montant' => 15000,
    ])->assertRedirect();

    $course = $this->course->fresh();

    expect($course->total_depenses)->toBe(100000.0)
        ->and($course->montant_facture_total)->toBe(1500000.0)
        ->and($course->montant_net)->toBe(1400000.0)
        ->and($course->expenses()->pluck('user_id')->unique()->all())->toEqual([$this->driver->id]);
});

it('refuse une dépense avec des décimales (franc guinéen)', function () {
    $this->actingAs($this->driver)
        ->post(route('chauffeur.courses.expenses.store', $this->course->id), [
            'type' => 'carburant',
            'montant' => 1500.5,
        ])
        ->assertSessionHasErrors('montant');

    expect($this->course->expenses()->count())->toBe(0);
});


it('verrouille la course une fois livrée', function () {
    $this->actingAs($this->driver);
    $this->post(route('chauffeur.courses.deliver', $this->course->id));

    $this->post(route('chauffeur.courses.expenses.store', $this->course->id), [
        'type' => 'autre',
        'montant' => 5,
    ])->assertStatus(422);

    expect(fn () => $this->course->fresh()->update(['adresse_arrivee' => 'Ailleurs']))
        ->toThrow(RuntimeException::class, 'ne peut plus être modifiée');

    expect(fn () => $this->course->fresh()->delete())
        ->toThrow(RuntimeException::class);
});

it('permet de rouvrir une course livrée, et seulement celle-là', function () {
    expect(fn () => $this->course->rouvrir())->toThrow(RuntimeException::class);

    $this->actingAs($this->driver);
    $this->post(route('chauffeur.courses.deliver', $this->course->id));

    $course = $this->course->fresh();
    $course->rouvrir();

    $course = $course->fresh();

    expect($course->statut)->toBe('en_cours')
        ->and($course->livree_at)->toBeNull()
        ->and($course->statusHistories()->orderBy('id')->pluck('statut')->all())
        ->toBe(['planifiee', 'livree', 'en_cours']);
});

it('calcule le retard automatiquement', function () {
    $enRetard = makeCourse($this->company, makeVehicle($this->company), [
        'statut' => 'en_cours',
        'date_heure' => now()->subHours(6),
        'livraison_prevue_at' => now()->subHour(),
    ]);

    $livreeEnRetard = makeCourse($this->company, makeVehicle($this->company), [
        'statut' => 'livree',
        'date_heure' => now()->subDays(2),
        'livraison_prevue_at' => now()->subDays(2)->addHours(8),
        'livree_at' => now()->subDays(2)->addHours(11),
    ]);

    $livreeAHeure = makeCourse($this->company, makeVehicle($this->company), [
        'statut' => 'livree',
        'date_heure' => now()->subDays(2),
        'livraison_prevue_at' => now()->subDays(2)->addHours(8),
        'livree_at' => now()->subDays(2)->addHours(7),
    ]);

    $annulee = makeCourse($this->company, makeVehicle($this->company), [
        'statut' => 'annulee',
        'date_heure' => now()->subDays(2),
        'livraison_prevue_at' => now()->subDays(2)->addHours(8),
    ]);

    expect($enRetard->statut_affiche)->toBe('En retard')
        ->and($livreeEnRetard->statut_affiche)->toBe('Livrée en retard')
        ->and($livreeAHeure->statut_affiche)->toBe('Livrée')
        ->and($annulee->statut_affiche)->toBe('Annulée')
        ->and($this->course->statut_affiche)->toBe('Planifiée');
});

it('refuse deux courses qui se chevauchent sur le même véhicule', function () {
    expect(fn () => makeCourse($this->company, $this->vehicle, [
        'date_heure' => now()->addDay()->setTime(12, 0),
        'livraison_prevue_at' => now()->addDay()->setTime(18, 0),
    ]))->toThrow(RuntimeException::class, 'déjà affecté');
});

it('accepte des créneaux qui se touchent ou sur un autre véhicule', function () {
    // Commence à 16 h, quand la première se termine.
    makeCourse($this->company, $this->vehicle, [
        'date_heure' => now()->addDay()->setTime(16, 0),
        'livraison_prevue_at' => now()->addDay()->setTime(20, 0),
    ]);

    // Même créneau, autre véhicule.
    makeCourse($this->company, makeVehicle($this->company));

    expect(Course::count())->toBe(3);
});

it("libère le créneau quand une course est annulée", function () {
    $this->course->update(['statut' => 'annulee']);

    makeCourse($this->company, $this->vehicle);

    expect(Course::where('vehicle_id', $this->vehicle->id)->count())->toBe(2);
});

it('libère la fin du créneau si la livraison a eu lieu plus tôt', function () {
    $this->course->update([
        'statut' => 'livree',
        'livree_at' => now()->addDay()->setTime(10, 0),
    ]);

    makeCourse($this->company, $this->vehicle, [
        'date_heure' => now()->addDay()->setTime(12, 0),
        'livraison_prevue_at' => now()->addDay()->setTime(15, 0),
    ]);

    expect(Course::where('vehicle_id', $this->vehicle->id)->count())->toBe(2);
});