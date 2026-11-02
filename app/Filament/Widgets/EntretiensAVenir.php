<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\VehicleResource;
use App\Models\Vehicle;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EntretiensAVenir extends Widget
{
    protected static string $view = 'filament.widgets.entretiens-a-venir';

    protected static ?int $sort = -9;

    protected int|string|array $columnSpan = 'full';

    /** Un véhicule est « à surveiller » si l'entretien est dépassé ou prévu sous 14 jours. */
    private function aSurveiller(): Builder
    {
        return Vehicle::query()
            ->where('company_id', auth()->user()->company_id)
            ->whereNotNull('prochain_entretien')
            ->whereDate('prochain_entretien', '<=', today()->addDays(14));
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->role === 'responsable' 
            && Vehicle::where('company_id', $user->company_id)
                ->whereNotNull('prochain_entretien')
                ->whereDate('prochain_entretien', '<=', today()->addDays(14))
                ->exists();
    }

    public function getEnRetard(): int
    {
        return (clone $this->aSurveiller())->whereDate('prochain_entretien', '<', today())->count();
    }

    public function getTotal(): int
    {
        return $this->aSurveiller()->count();
    }

    public function getVehicules(): Collection
    {
        return $this->aSurveiller()->orderBy('prochain_entretien')->take(4)->get();
    }

    public function getUrl(): string
    {
        return VehicleResource::getUrl('index', [
            'tableFilters' => ['entretien_a_prevoir' => ['isActive' => true]],
        ]);
    }
}
