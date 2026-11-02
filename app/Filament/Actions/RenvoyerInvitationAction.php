<?php

namespace App\Filament\Actions;

use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class RenvoyerInvitationAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'renvoyer_invitation';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label("Renvoyer l'invitation")
            ->icon('heroicon-o-envelope')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading("Renvoyer l'invitation ?")
            ->modalDescription('Un nouveau lien pour choisir son mot de passe sera envoyé par email.')
            ->action(function (User $record): void {
                if ($record->envoyerInvitation()) {
                    Notification::make()->success()->title('Invitation envoyée à '.$record->email)->send();
                } else {
                    Notification::make()->danger()->title("L'email n'a pas pu être envoyé")->send();
                }
            });
    }
}
