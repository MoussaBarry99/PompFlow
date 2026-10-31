<?php

beforeEach(function () {
    $this->withoutVite();
});

it("affiche la page d'accueil aux visiteurs", function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Espace chauffeur')
        ->assertSee('Espace responsable');
});

it('redirige un visiteur non connecté vers la connexion', function () {
    $this->get('/admin')->assertRedirect(route('filament.admin.auth.login'));
    $this->get('/chauffeur/courses')->assertRedirect(route('login'));
});

it("ferme l'inscription publique", function () {
    $this->get('/register')->assertNotFound();
});

it('envoie chaque rôle vers son espace après connexion', function () {
    $this->actingAs(makeUser('responsable', makeCompany('A')))
        ->get('/dashboard')
        ->assertRedirect('/admin');

    $this->actingAs(makeUser('chauffeur', makeCompany('B')))
        ->get('/dashboard')
        ->assertRedirect(route('chauffeur.courses.index'));
});

it('limite le panel Filament aux responsables et au super admin', function () {
    $company = makeCompany();

    $this->actingAs(makeUser('chauffeur', $company))->get('/admin')->assertForbidden();
    $this->actingAs(makeUser('employe', $company))->get('/admin')->assertForbidden();
    $this->actingAs(makeUser('responsable', $company))->get('/admin')->assertOk();
    $this->actingAs(makeUser('super_admin'))->get('/admin')->assertOk();
});

it('réserve la gestion des sociétés au super admin', function () {
    $company = makeCompany();

    $this->actingAs(makeUser('responsable', $company))->get('/admin/companies')->assertForbidden();
    $this->actingAs(makeUser('super_admin'))->get('/admin/companies')->assertOk();
});

it("réserve l'espace chauffeur aux chauffeurs et aux employés", function () {
    $company = makeCompany();

    $this->actingAs(makeUser('responsable', $company))->get('/chauffeur/courses')->assertForbidden();
    $this->actingAs(makeUser('chauffeur', $company))->get('/chauffeur/courses')->assertOk();
    $this->actingAs(makeUser('employe', $company))->get('/chauffeur/courses')->assertOk();
});