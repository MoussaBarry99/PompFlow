<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleIssue;
use Illuminate\Http\Request;

class VehiculeController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $vehicleId = $user->assignedVehicleId();

        $vehicle = $vehicleId
            ? Vehicle::where('company_id', $user->company_id)->find($vehicleId)
            : null;

        $issues = $vehicle
            ? VehicleIssue::where('vehicle_id', $vehicle->id)->latest()->take(10)->get()
            : collect();

        return view('chauffeur.vehicule.show', compact('vehicle', 'issues'));
    }
}
