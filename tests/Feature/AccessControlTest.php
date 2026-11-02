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

it('limite le panel Filament selon le rôle', function (string $role, int $statut) {
    $company = makeCompany();
    $user = $role === 'super_admin' ? makeUser($role) : makeUser($role, $company);

    $this->actingAs($user)->get('/admin')->assertStatus($statut);
})->with([
    'chauffeur' => ['chauffeur', 403],
    'employé' => ['employe', 403],
    'responsable' => ['responsable', 200],
    'super admin' => ['super_admin', 200],
]);

it('réserve la gestion des sociétés au super admin', function (string $role, int $statut) {
    $company = makeCompany();
    $user = $role === 'super_admin' ? makeUser($role) : makeUser($role, $company);

    $this->actingAs($user)->get('/admin/companies')->assertStatus($statut);
})->with([
    'responsable' => ['responsable', 403],
    'super admin' => ['super_admin', 200],
]);
it("réserve l'espace chauffeur aux chauffeurs et aux employés", function () {
    $company = makeCompany();

    $this->actingAs(makeUser('responsable', $company))->get('/chauffeur/courses')->assertForbidden();
    $this->actingAs(makeUser('chauffeur', $company))->get('/chauffeur/courses')->assertOk();
    $this->actingAs(makeUser('employe', $company))->get('/chauffeur/courses')->assertOk();
});
