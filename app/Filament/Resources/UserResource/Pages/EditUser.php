<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['vehicule_conduit'] = $this->record->drivenVehicle?->id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['role'] ?? null) === 'chauffeur') {
            $data['vehicle_id'] = null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $vehicleId = $this->record->role === 'chauffeur'
            ? ($this->data['vehicule_conduit'] ?? null)
            : null;

        if (! $this->record->affecterVehicule($vehicleId ? (int) $vehicleId : null)) {
            Notification::make()
                ->title('Véhicule non affecté')
                ->body('Ce véhicule est déjà pris par un autre chauffeur. Le reste des modifications est enregistré.')
                ->warning()
                ->send();
        }
    }
}