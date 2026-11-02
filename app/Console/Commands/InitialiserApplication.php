<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Crée le premier super admin à partir des variables d'environnement
 * SUPER_ADMIN_EMAIL / SUPER_ADMIN_PASSWORD (et SUPER_ADMIN_NAME, optionnelle).
 * Ne fait rien s'il existe déjà un super admin : on peut donc la lancer à chaque démarrage.
 */
class InitialiserApplication extends Command
{
    protected $signature = 'pompflow:initialiser';

    protected $description = 'Crée le premier super admin depuis les variables d\'environnement (si aucun n\'existe)';

    public function handle(): int
    {
        if (User::where('role', 'super_admin')->exists()) {
            $this->info('Un super admin existe déjà : rien à faire.');

            return self::SUCCESS;
        }

        $email = getenv('SUPER_ADMIN_EMAIL') ?: null;
        $motDePasse = getenv('SUPER_ADMIN_PASSWORD') ?: null;

        if (! $email || ! $motDePasse) {
            $this->warn('SUPER_ADMIN_EMAIL / SUPER_ADMIN_PASSWORD absents : aucun super admin créé.');

            return self::SUCCESS;
        }

        if (strlen($motDePasse) < 12) {
            $this->error('SUPER_ADMIN_PASSWORD doit faire au moins 12 caractères.');

            return self::FAILURE;
        }

        $utilisateur = new User();
        $utilisateur->forceFill([
            'name' => getenv('SUPER_ADMIN_NAME') ?: 'Super Admin',
            'email' => $email,
            'password' => Hash::make($motDePasse),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ])->save();

        $this->info("Super admin créé : {$email}. Supprime maintenant SUPER_ADMIN_PASSWORD de l'environnement.");

        return self::SUCCESS;
    }
}
