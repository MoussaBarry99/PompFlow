<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $vehicleId = $this->data['vehicule_conduit'] ?? null;

        if ($this->record->role !== 'chauffeur' || ! $vehicleId) {
            return;
        }

        if (! $this->record->affecterVehicule((int) $vehicleId)) {
            Notification::make()
                ->title('Véhicule non affecté')
                ->body('Ce véhicule est déjà pris. Le chauffeur est enregistré : affecte-lui un véhicule depuis sa fiche.')
                ->warning()
                ->send();
        }
    }
}