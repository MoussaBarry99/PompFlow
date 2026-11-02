<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

afterEach(function () {
    putenv('SUPER_ADMIN_EMAIL');
    putenv('SUPER_ADMIN_PASSWORD');
});

it("crée le super admin depuis l'environnement, une seule fois", function () {
    putenv('SUPER_ADMIN_EMAIL=admin@exemple.test');
    putenv('SUPER_ADMIN_PASSWORD=un-mot-de-passe-solide');

    $this->artisan('pompflow:initialiser')->assertSuccessful();

    $admin = User::where('email', 'admin@exemple.test')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->role)->toBe('super_admin')
        ->and(Hash::check('un-mot-de-passe-solide', $admin->password))->toBeTrue();

    putenv('SUPER_ADMIN_EMAIL=autre@exemple.test');
    $this->artisan('pompflow:initialiser')->assertSuccessful();

    expect(User::where('role', 'super_admin')->count())->toBe(1);
});

it('refuse un mot de passe trop court', function () {
    putenv('SUPER_ADMIN_EMAIL=admin@exemple.test');
    putenv('SUPER_ADMIN_PASSWORD=court');

    $this->artisan('pompflow:initialiser')->assertFailed();

    expect(User::where('role', 'super_admin')->count())->toBe(0);
});
