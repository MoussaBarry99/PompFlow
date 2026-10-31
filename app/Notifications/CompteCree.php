<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompteCree extends Notification
{
    private const ROLES = [
        'responsable' => 'responsable',
        'chauffeur' => 'chauffeur',
        'employe' => 'employé',
        'super_admin' => 'administrateur',
    ];

    public function __construct(private string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $societe = $notifiable->company?->libelle;
        $role = self::ROLES[$notifiable->role] ?? $notifiable->role;

        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ]);

        return (new MailMessage)
            ->subject('Bienvenue sur PompFlow'.($societe ? ' — '.$societe : ''))
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line($societe
                ? "Un compte {$role} vient d'être créé pour vous au sein de la société {$societe}."
                : "Un compte {$role} vient d'être créé pour vous.")
            ->line('Votre identifiant de connexion : '.$notifiable->email)
            ->action('Choisir mon mot de passe', $url)
            ->line('Ce lien est valable 3 jours. Passé ce délai, utilisez « Mot de passe oublié » sur la page de connexion.')
            ->salutation("L'équipe PompFlow");
    }
}
