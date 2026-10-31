<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class DriverCourseController extends Controller
{
    private const OPEN = ['planifiee', 'en_cours'];

    public function index()
    {
        $courses = $this->coursesQuery()
            ->with('clients')
            ->orderByDesc('date_heure')
            ->get();

        return view('chauffeur.courses.index', compact('courses'));
    }

    public function show(int $course)
    {
        $record = $this->coursesQuery()
            ->with(['courseClients.client', 'expenses'])
            ->findOrFail($course);

        return view('chauffeur.courses.show', ['course' => $record]);
    }

    public function start(int $course)
    {
        $record = $this->findForDriver($course);

        abort_unless($record->statut === 'planifiee', 422);

        $record->update(['statut' => 'en_cours']);

        return back()->with('status', 'Course démarrée.');
    }

    public function deliver(int $course)
    {
        $record = $this->findForDriver($course);

        abort_unless(in_array($record->statut, self::OPEN, true), 422);

        $record->update(['statut' => 'livree']);

        return back()->with('status', 'Livraison validée.');
    }

    public function storeExpense(Request $request, int $course)
    {
        $record = $this->findForDriver($course);

        abort_unless(in_array($record->statut, self::OPEN, true), 422);

        $data = $request->validate([
            'type' => 'required|in:carburant,peage,repas,autre',
            'montant' => 'required|integer|min:1|max:999999999',
            'description' => 'nullable|string|max:255',
        ]);

        $record->expenses()->create($data + [
            'company_id' => $record->company_id,
            'user_id' => auth()->id(),
        ]);

        return back()->with('status', 'Dépense enregistrée.');
    }

    public function destroyExpense(int $course, int $expense)
    {
        $record = $this->findForDriver($course);

        abort_unless(in_array($record->statut, self::OPEN, true), 422);

        $record->expenses()
            ->where('user_id', auth()->id())
            ->findOrFail($expense)
            ->delete();

        return back()->with('status', 'Dépense supprimée.');
    }

    private function coursesQuery()
    {
        $user = auth()->user();
        $vehicleId = $user->assignedVehicleId();

        return Course::query()
            ->where('company_id', $user->company_id)
            ->when(
                $vehicleId,
                fn ($query) => $query->where('vehicle_id', $vehicleId),
                fn ($query) => $query->whereRaw('1 = 0')
            );
    }

    private function findForDriver(int $id): Course
    {
        abort_unless(auth()->user()->role === 'chauffeur', 403);

        return $this->coursesQuery()->findOrFail($id);
    }
}