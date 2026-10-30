<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\Course;
use App\Models\CourseClient;
use App\Models\CourseExpense;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $this->superAdmin();

        [$conakry, $chef1] = $this->entreprise(
            'Conakry Express',
            'Kaloum, Conakry',
            ['email' => 'responsable1@demo.test', 'nom' => 'Mariama Diallo'],
            [
                [
                    'immat' => 'GN-1001-A',
                    'type' => 'Camion 10 tonnes',
                    'capacite' => 10000,
                    'chauffeur' => ['email' => 'chauffeur1@demo.test', 'nom' => 'Alpha Camara'],
                    'employes' => [
                        ['email' => 'employe1@demo.test', 'nom' => 'Sékou Bah'],
                        ['email' => 'employe2@demo.test', 'nom' => 'Fanta Sylla'],
                    ],
                ],
                [
                    'immat' => 'GN-1002-A',
                    'type' => 'Camionnette 3 tonnes',
                    'capacite' => 3000,
                    'chauffeur' => ['email' => 'chauffeur2@demo.test', 'nom' => 'Ibrahima Barry'],
                    'employes' => [
                        ['email' => 'employe3@demo.test', 'nom' => 'Mamadou Soumah'],
                        ['email' => 'employe4@demo.test', 'nom' => 'Kadiatou Keita'],
                    ],
                ],
            ]
        );

        [$fouta, $chef2] = $this->entreprise(
            'Fouta Logistique',
            'Labé',
            ['email' => 'responsable2@demo.test', 'nom' => 'Oumar Baldé'],
            [
                [
                    'immat' => 'GN-2001-B',
                    'type' => 'Camion 5 tonnes',
                    'capacite' => 5000,
                    'chauffeur' => ['email' => 'chauffeur3@demo.test', 'nom' => 'Thierno Sow'],
                    'employes' => [
                        ['email' => 'employe5@demo.test', 'nom' => 'Aïssatou Diallo'],
                        ['email' => 'employe6@demo.test', 'nom' => 'Boubacar Camara'],
                    ],
                ],
            ]
        );

        $this->coursesConakry($conakry, $chef1);
        $this->coursesFouta($fouta, $chef2);

        Model::reguard();

        $this->afficherComptes();
    }

    private function superAdmin(): void
    {
        if (User::where('email', 'moussa@pompflow.com')->exists()) {
            return;
        }

        User::create([
            'name' => 'Moussa Admin',
            'email' => 'moussa@pompflow.com',
            'password' => 'password123',
            'role' => 'super_admin',
        ]);
    }

    private function user(string $email, string $nom, string $role, int $companyId, ?int $vehicleId = null): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $nom,
                'password' => 'password',
                'role' => $role,
                'company_id' => $companyId,
                'vehicle_id' => $vehicleId,
            ]
        );
    }

    private function entreprise(string $nom, string $adresse, array $responsable, array $vehicules): array
    {
        $company = Company::updateOrCreate(['nom' => $nom], ['adresse' => $adresse]);

        $chef = $this->user($responsable['email'], $responsable['nom'], 'responsable', $company->id);

        foreach ($vehicules as $data) {
            $chauffeur = $this->user($data['chauffeur']['email'], $data['chauffeur']['nom'], 'chauffeur', $company->id);

            $vehicle = Vehicle::updateOrCreate(
                ['immatriculation' => $data['immat']],
                [
                    'company_id' => $company->id,
                    'driver_id' => $chauffeur->id,
                    'type' => $data['type'],
                    'capacite' => $data['capacite'],
                    'statut_entretien' => 'ok',
                ]
            );

            foreach ($data['employes'] as $employe) {
                $this->user($employe['email'], $employe['nom'], 'employe', $company->id, $vehicle->id);
            }
        }

        return [$company, $chef];
    }

    private function client(Company $company, string $nom, string $contact): Client
    {
        return Client::updateOrCreate(
            ['company_id' => $company->id, 'nom' => $nom],
            ['contact' => $contact]
        );
    }

    private function course(Company $company, User $chef, Vehicle $vehicle, array $d): void
    {
        $course = Course::create([
            'company_id' => $company->id,
            'vehicle_id' => $vehicle->id,
            'cree_par_user_id' => $chef->id,
            'adresse_depart' => $d['depart'],
            'adresse_arrivee' => $d['arrivee'],
            'date_heure' => $d['depart_a'],
            'livraison_prevue_at' => $d['prevue_a'],
            'statut' => 'planifiee',
        ]);

        foreach ($d['clients'] as [$client, $montant, $marchandise, $poids]) {
            CourseClient::create([
                'course_id' => $course->id,
                'client_id' => $client->id,
                'montant_facture' => $montant,
                'type_marchandise' => $marchandise,
                'poids_kg' => $poids,
            ]);
        }

        foreach ($d['depenses'] ?? [] as [$type, $montant, $description]) {
            CourseExpense::create([
                'company_id' => $company->id,
                'course_id' => $course->id,
                'user_id' => $vehicle->driver_id,
                'type' => $type,
                'description' => $description,
                'montant' => $montant,
            ]);
        }

        if ($d['statut'] === 'en_cours') {
            $course->update(['statut' => 'en_cours']);
        } elseif ($d['statut'] === 'livree') {
            $course->update(['statut' => 'en_cours']);
            $course->update(['statut' => 'livree', 'livree_at' => $d['livree_a']]);
        } elseif ($d['statut'] === 'annulee') {
            $course->update(['statut' => 'annulee']);
        }
    }

    private function coursesConakry(Company $company, User $chef): void
    {
        if (Course::where('company_id', $company->id)->exists()) {
            return;
        }

        [$v1, $v2] = Vehicle::where('company_id', $company->id)->orderBy('id')->get()->all();

        $madina = $this->client($company, 'Marché Madina', 'madina@demo.test');
        $kaloum = $this->client($company, 'Quincaillerie Kaloum', 'kaloum@demo.test');
        $dalaba = $this->client($company, 'Coopérative de Dalaba', 'dalaba@demo.test');

        $hier = now()->subDay();
        $avantHier = now()->subDays(2);

        // 1. Livrée à l'heure, deux clients
        $this->course($company, $chef, $v1, [
            'depart' => 'Conakry',
            'arrivee' => 'Kindia',
            'depart_a' => $hier->copy()->setTime(7, 0),
            'prevue_a' => $hier->copy()->setTime(15, 0),
            'statut' => 'livree',
            'livree_a' => $hier->copy()->setTime(13, 40),
            'clients' => [
                [$madina, 1500, 'Riz', 2000],
                [$kaloum, 900, 'Huile', 600],
            ],
            'depenses' => [
                ['carburant', 85.50, 'Plein'],
                ['peage', 12, null],
                ['repas', 8, 'Déjeuner'],
            ],
        ]);

        // 2. Livrée en retard
        $this->course($company, $chef, $v2, [
            'depart' => 'Conakry',
            'arrivee' => 'Mamou',
            'depart_a' => $avantHier->copy()->setTime(6, 0),
            'prevue_a' => $avantHier->copy()->setTime(14, 0),
            'statut' => 'livree',
            'livree_a' => $avantHier->copy()->setTime(17, 30),
            'clients' => [
                [$kaloum, 1200, 'Ciment', 5000],
            ],
            'depenses' => [
                ['carburant', 120, 'Plein'],
                ['autre', 25, 'Réparation pneu'],
            ],
        ]);

        // 3. En cours
        $this->course($company, $chef, $v1, [
            'depart' => 'Conakry',
            'arrivee' => 'Dalaba',
            'depart_a' => now()->subHours(2),
            'prevue_a' => now()->addHours(3),
            'statut' => 'en_cours',
            'clients' => [
                [$dalaba, 800, 'Légumes', 400],
            ],
            'depenses' => [
                ['carburant', 60, 'Plein'],
            ],
        ]);

        // 4. En retard (en cours, livraison prévue dépassée)
        $this->course($company, $chef, $v2, [
            'depart' => 'Conakry',
            'arrivee' => 'Boké',
            'depart_a' => now()->subHours(5),
            'prevue_a' => now()->subHour(),
            'statut' => 'en_cours',
            'clients' => [
                [$madina, 650, 'Matériaux', 1500],
            ],
        ]);

        // 5. Planifiée
        $this->course($company, $chef, $v1, [
            'depart' => 'Conakry',
            'arrivee' => 'Kankan',
            'depart_a' => now()->addHours(3),
            'prevue_a' => now()->addHours(8),
            'statut' => 'planifiee',
            'clients' => [
                [$kaloum, 1000, 'Meubles', 800],
            ],
        ]);

        // 6. Annulée
        $this->course($company, $chef, $v2, [
            'depart' => 'Conakry',
            'arrivee' => 'Labé',
            'depart_a' => now()->addDay(),
            'prevue_a' => now()->addDay()->addHours(6),
            'statut' => 'annulee',
            'clients' => [
                [$dalaba, 500, 'Colis', 50],
            ],
        ]);
    }

    private function coursesFouta(Company $company, User $chef): void
    {
        if (Course::where('company_id', $company->id)->exists()) {
            return;
        }

        $vehicle = Vehicle::where('company_id', $company->id)->orderBy('id')->first();

        $labe = $this->client($company, 'Marché de Labé', 'labe@demo.test');
        $pita = $this->client($company, 'Scierie de Pita', 'pita@demo.test');

        $hier = now()->subDay();

        $this->course($company, $chef, $vehicle, [
            'depart' => 'Labé',
            'arrivee' => 'Pita',
            'depart_a' => $hier->copy()->setTime(8, 0),
            'prevue_a' => $hier->copy()->setTime(12, 0),
            'statut' => 'livree',
            'livree_a' => $hier->copy()->setTime(11, 30),
            'clients' => [
                [$labe, 700, 'Poissons', 300],
            ],
            'depenses' => [
                ['carburant', 40, 'Plein'],
            ],
        ]);

        $this->course($company, $chef, $vehicle, [
            'depart' => 'Labé',
            'arrivee' => 'Mamou',
            'depart_a' => now()->addDay(),
            'prevue_a' => now()->addDay()->addHours(5),
            'statut' => 'planifiee',
            'clients' => [
                [$pita, 450, 'Bois', 900],
            ],
        ]);
    }

    private function afficherComptes(): void
    {
        if (! $this->command) {
            return;
        }

        $this->command->table(
            ['Rôle', 'Email', 'Mot de passe'],
            [
                ['Super admin', 'moussa@pompflow.com', "password123 (créé s'il n'existait pas)"],
                ['Responsable Conakry Express', 'responsable1@demo.test', 'password'],
                ['Chauffeur GN-1001-A', 'chauffeur1@demo.test', 'password'],
                ['Chauffeur GN-1002-A', 'chauffeur2@demo.test', 'password'],
                ['Employés Conakry Express', 'employe1 à employe4@demo.test', 'password'],
                ['Responsable Fouta Logistique', 'responsable2@demo.test', 'password'],
                ['Chauffeur GN-2001-B', 'chauffeur3@demo.test', 'password'],
                ['Employés Fouta Logistique', 'employe5 et employe6@demo.test', 'password'],
            ]
        );
    }
}