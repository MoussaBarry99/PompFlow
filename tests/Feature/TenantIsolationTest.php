<?php

beforeEach(function () {
    $this->withoutVite();

    $this->a = makeCompany('Société A');
    $this->b = makeCompany('Société B');

    $this->chefA = makeUser('responsable', $this->a);
    $this->driverA = makeUser('chauffeur', $this->a, ['name' => 'Chauffeur Alpha']);
    $this->driverB = makeUser('chauffeur', $this->b, ['name' => 'Chauffeur Beta']);

    $this->vehicleA = makeVehicle($this->a, $this->driverA, 'AAA-111-AA');
    $this->vehicleB = makeVehicle($this->b, $this->driverB, 'BBB-222-BB');

    makeClient($this->a, 'Client Alpha');
    makeClient($this->b, 'Client Beta');

    $this->courseA = makeCourse($this->a, $this->vehicleA, ['adresse_arrivee' => 'Kankan']);
    $this->courseB = makeCourse($this->b, $this->vehicleB, ['adresse_arrivee' => 'Siguiri']);
});

it("n'affiche à un responsable que les données de sa société", function () {
    $this->actingAs($this->chefA);

    $this->get('/admin/courses')->assertOk()->assertSee('Kankan')->assertDontSee('Siguiri');
    $this->get('/admin/vehicles')->assertOk()->assertSee('AAA-111-AA')->assertDontSee('BBB-222-BB');
    $this->get('/admin/clients')->assertOk()->assertSee('Client Alpha')->assertDontSee('Client Beta');
    $this->get('/admin/users')->assertOk()->assertSee('Chauffeur Alpha')->assertDontSee('Chauffeur Beta');
});

it("refuse l'accès aux fiches d'une autre société", function () {
    $this->actingAs($this->chefA);

    $this->get("/admin/courses/{$this->courseA->id}")->assertOk();
    $this->get("/admin/courses/{$this->courseB->id}")->assertNotFound();
    $this->get("/admin/vehicles/{$this->vehicleB->id}/edit")->assertNotFound();
    $this->get("/admin/users/{$this->driverB->id}/edit")->assertNotFound();
});

it('ne montre à un chauffeur que les courses de son véhicule', function () {
    $autreChauffeur = makeUser('chauffeur', $this->a);
    makeCourse($this->a, makeVehicle($this->a, $autreChauffeur), ['adresse_arrivee' => 'Faranah']);

    $this->actingAs($this->driverA)
        ->get('/chauffeur/courses')
        ->assertOk()
        ->assertSee('Kankan')
        ->assertDontSee('Faranah')
        ->assertDontSee('Siguiri');
});

it("refuse à un chauffeur la course d'un autre véhicule ou d'une autre société", function () {
    $autre = makeUser('chauffeur', $this->a);
    $autreCourse = makeCourse($this->a, makeVehicle($this->a, $autre));

    $this->actingAs($this->driverA);

    $this->get(route('chauffeur.courses.show', $this->courseA->id))->assertOk();
    $this->get(route('chauffeur.courses.show', $autreCourse->id))->assertNotFound();
    $this->get(route('chauffeur.courses.show', $this->courseB->id))->assertNotFound();
    $this->post(route('chauffeur.courses.start', $autreCourse->id))->assertNotFound();
    $this->post(route('chauffeur.courses.deliver', $this->courseB->id))->assertNotFound();
});

it('ne montre jamais le montant facturé au chauffeur', function () {
    $course = makeCourse($this->a, $this->vehicleA, [
        'date_heure' => now()->addDays(3)->setTime(8, 0),
        'livraison_prevue_at' => now()->addDays(3)->setTime(16, 0),
    ], 98765.43);

    $this->actingAs($this->driverA)
        ->get(route('chauffeur.courses.show', $course->id))
        ->assertOk()
        ->assertDontSee('98765')
        ->assertDontSee('98 765');
});

it('laisse un employé consulter sans agir', function () {
    $employe = makeUser('employe', $this->a, ['vehicle_id' => $this->vehicleA->id]);

    $this->actingAs($employe);

    $this->get(route('chauffeur.courses.show', $this->courseA->id))
        ->assertOk()
        ->assertDontSee('Valider la livraison');

    $this->post(route('chauffeur.courses.deliver', $this->courseA->id))->assertForbidden();
    $this->post(route('chauffeur.courses.expenses.store', $this->courseA->id), [
        'type' => 'carburant',
        'montant' => 10,
    ])->assertForbidden();

    expect($this->courseA->fresh()->statut)->toBe('planifiee');
});