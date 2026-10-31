<?php

use App\Models\Client;
use App\Models\Company;
use App\Models\Course;
use App\Models\CourseClient;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');

/*
 * Fabriques de données pour les tests.
 */
function makeCompany(string $nom = 'Société Test'): Company
{
    return Model::unguarded(fn () => Company::create(['nom' => $nom]));
}

function makeUser(string $role, ?Company $company = null, array $attrs = []): User
{
    return Model::unguarded(fn () => User::create(array_merge([
        'name' => ucfirst($role) . ' ' . fake()->unique()->lastName(),
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => $role,
        'company_id' => $company?->id,
    ], $attrs)));
}

function makeVehicle(Company $company, ?User $driver = null, ?string $immat = null): Vehicle
{
    return Model::unguarded(fn () => Vehicle::create([
        'company_id' => $company->id,
        'driver_id' => $driver?->id,
        'immatriculation' => $immat ?? strtoupper(fake()->unique()->bothify('??-###-??')),
        'type' => 'Camion',
        'capacite' => 1000,
        'statut_entretien' => 'ok',
    ]));
}

function makeClient(Company $company, string $nom = 'Client Test'): Client
{
    return Model::unguarded(fn () => Client::create([
        'company_id' => $company->id,
        'nom' => $nom,
    ]));
}

/*
 * Par défaut : demain de 8 h à 16 h. Deux courses sur le même véhicule
 * doivent donc recevoir des horaires différents.
 */
function makeCourse(Company $company, ?Vehicle $vehicle, array $attrs = [], float $montant = 1000): Course
{
    $course = Model::unguarded(fn () => Course::create(array_merge([
        'company_id' => $company->id,
        'vehicle_id' => $vehicle?->id,
        'adresse_depart' => 'Conakry',
        'adresse_arrivee' => 'Kindia',
        'date_heure' => now()->addDay()->setTime(8, 0),
        'livraison_prevue_at' => now()->addDay()->setTime(16, 0),
        'statut' => 'planifiee',
    ], $attrs)));

    Model::unguarded(fn () => CourseClient::create([
        'course_id' => $course->id,
        'client_id' => makeClient($company, 'Client ' . $course->id)->id,
        'montant_facture' => $montant,
        'type_marchandise' => 'Riz',
    ]));

    return $course;
}