<?php

use App\Notifications\CompteCree;
use Illuminate\Support\Facades\Notification;

it('envoie un email de bienvenue avec un lien pour choisir son mot de passe', function () {
    Notification::fake();

    $company = makeCompany();
    $chauffeur = makeUser('chauffeur', $company);

    expect($chauffeur->envoyerInvitation())->toBeTrue();

    Notification::assertSentTo($chauffeur, CompteCree::class, function (CompteCree $notification) use ($chauffeur, $company) {
        $mail = $notification->toMail($chauffeur);

        return str_contains($mail->actionUrl, 'reset-password')
            && str_contains($mail->actionUrl, urlencode($chauffeur->email))
            && str_contains($mail->subject, $company->libelle);
    });
});

it("n'envoie aucune invitation au super admin", function () {
    Notification::fake();

    $admin = makeUser('super_admin');

    expect($admin->envoyerInvitation())->toBeFalse();
    Notification::assertNothingSent();
});
