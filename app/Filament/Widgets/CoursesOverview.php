<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\CourseClient;
use App\Models\CourseExpense;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CoursesOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()->role === 'responsable';
    }

    protected function getStats(): array
    {
        $companyId = auth()->user()->company_id;

        $aujourdhui = Course::where('company_id', $companyId)
            ->whereDate('date_heure', today())
            ->where('statut', '!=', 'annulee')
            ->count();

        $enCours = Course::where('company_id', $companyId)
            ->where('statut', 'en_cours')
            ->count();

        $enRetard = Course::where('company_id', $companyId)
            ->whereNotIn('statut', ['livree', 'annulee'])
            ->where('livraison_prevue_at', '<', now())
            ->count();

        $totalVehicules = Vehicle::where('company_id', $companyId)->count();

        $disponibles = Vehicle::where('company_id', $companyId)
            ->where('statut_entretien', 'ok')
            ->whereDoesntHave('courses', fn ($query) => $query->where('statut', 'en_cours'))
            ->count();

        $livreesDuMois = fn ($query) => $query
            ->where('courses.company_id', $companyId)
            ->where('courses.statut', 'livree')
            ->whereBetween('courses.livree_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $facture = CourseClient::whereHas('course', $livreesDuMois)->sum('montant_facture');
        $depenses = CourseExpense::whereHas('course', $livreesDuMois)->sum('montant');
        $net = $facture - $depenses;

        return [
            Stat::make('Courses du jour', $aujourdhui)
                ->description('Départs prévus ce jour')
                ->icon('heroicon-o-calendar-days'),
            Stat::make('En cours', $enCours)
                ->description('Actuellement sur la route')
                ->color('info')
                ->icon('heroicon-o-truck'),
            Stat::make('En retard', $enRetard)
                ->description($enRetard > 0 ? 'À traiter en priorité' : 'Aucun retard')
                ->color($enRetard > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
            Stat::make('Véhicules disponibles', $disponibles . ' / ' . $totalVehicules)
                ->description('Hors entretien et hors course')
                ->icon('heroicon-o-key'),
            Stat::make('Facturé ce mois', $this->euro($facture))
                ->description('Courses livrées')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Net ce mois', $this->euro($net))
                ->description('Après ' . $this->euro($depenses) . ' de dépenses')
                ->color($net < 0 ? 'danger' : 'success')
                ->icon('heroicon-o-currency-euro'),
        ];
    }

    private function euro(float|int|string $value): string
    {
        return number_format((float) $value, 2, ',', ' ') . ' €';
    }
}