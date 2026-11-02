<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\VehicleIssueResource;
use App\Models\VehicleIssue;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class ProblemesVehicules extends Widget
{
    protected static string $view = 'filament.widgets.problemes-vehicules';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->role === 'responsable'
            && VehicleIssue::where('company_id', $user->company_id)->where('statut', 'ouvert')->exists();
    }

    public function getTotal(): int
    {
        return VehicleIssue::where('company_id', auth()->user()->company_id)
            ->where('statut', 'ouvert')
            ->count();
    }

    public function getRecents(): Collection
    {
        return VehicleIssue::with(['vehicle', 'user'])
            ->where('company_id', auth()->user()->company_id)
            ->where('statut', 'ouvert')
            ->orderByRaw("CASE gravite WHEN 'urgente' THEN 0 WHEN 'moyenne' THEN 1 ELSE 2 END")
            ->latest()
            ->take(3)
            ->get();
    }

    public function getUrl(): string
    {
        return VehicleIssueResource::getUrl('index');
    }
}
