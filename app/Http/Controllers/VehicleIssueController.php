<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleIssue;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleIssueController extends Controller
{
    public function create()
    {
        return redirect()->route('chauffeur.vehicule');
    }

    public function store(Request $request)
    {
        $vehicle = $this->vehicleOf($request);

        if (! $vehicle) {
            return back()->withErrors(['type' => "Aucun véhicule ne t'est affecté."]);
        }

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(VehicleIssue::TYPES))],
            'gravite' => ['required', Rule::in(array_keys(VehicleIssue::GRAVITES))],
            'description' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        VehicleIssue::create($data + [
            'company_id' => $request->user()->company_id,
            'vehicle_id' => $vehicle->id,
            'user_id' => $request->user()->id,
        ]);

        return redirect()
                ->route('chauffeur.vehicule')
                ->withFragment('signaler')
                ->with('status', 'Signalement envoyé au responsable.');
    }

    private function vehicleOf(Request $request): ?Vehicle
    {
        $user = $request->user();
        $vehicleId = $user->assignedVehicleId();

        return $vehicleId
            ? Vehicle::where('company_id', $user->company_id)->find($vehicleId)
            : null;
    }
    
}
