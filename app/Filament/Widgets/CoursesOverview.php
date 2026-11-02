<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CourseResource;
use App\Filament\Resources\VehicleResource;
use App\Models\Course;
use App\Models\CourseClient;
use App\Models\CourseExpense;
use App\Models\Vehicle;
use App\Support\Money;
use Carbon\CarbonInterface;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CoursesOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    // Par défaut Filament recharge ce widget toutes les 5 s : on espace.
    protected static ?string $pollingInterval = '30s';

    protected function getColumns(): int
    {
        return 3;
    }

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

        [$facture, $depenses] = $this->totaux($companyId, now()->startOfMonth(), now());

        // Même période le mois précédent (du 1er au même jour) pour une comparaison juste.
        $moisPrecedent = now()->subMonthNoOverflow();
        [$facturePrecedent] = $this->totaux($companyId, $moisPrecedent->copy()->startOfMonth(), $moisPrecedent);

        $variation = $facturePrecedent > 0
            ? (int) round((($facture - $facturePrecedent) / $facturePrecedent) * 100)
            : null;

        $net = $facture - $depenses;

        return [
            Stat::make('Courses du jour', $aujourdhui)
                ->description('Départs prévus ce jour')
                ->icon('heroicon-o-calendar-days')
                ->chart($this->coursesParJour($companyId))
                ->url(CourseResource::getUrl('index')),

            Stat::make('En cours', $enCours)
                ->description('Actuellement sur la route')
                ->color('info')
                ->icon('heroicon-o-truck')
                ->url(CourseResource::getUrl('index', [
                    'tableFilters' => ['statut' => ['value' => 'en_cours']],
                ])),

            Stat::make('En retard', $enRetard)
                ->description($enRetard > 0 ? 'À traiter en priorité' : 'Aucun retard')
                ->color($enRetard > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle')
                ->url(CourseResource::getUrl('index')),

            Stat::make('Véhicules disponibles', $disponibles.' / '.$totalVehicules)
                ->description('Hors entretien et hors course')
                ->color($totalVehicules > 0 && $disponibles === 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-key')
                ->url(VehicleResource::getUrl('index')),

            Stat::make('Facturé ce mois', Money::format($facture))
                ->description($variation === null
                    ? 'Courses livrées'
                    : sprintf('%+d %% vs mois dernier', $variation))
                ->descriptionIcon($variation === null
                    ? null
                    : ($variation >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'))
                ->color($variation !== null && $variation < 0 ? 'warning' : 'success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Net ce mois', Money::format($net))
                ->description('Après '.Money::format($depenses).' de dépenses')
                ->color($net < 0 ? 'danger' : 'success')
                ->icon('heroicon-o-scale'),
        ];
    }

    /** @return array{0: float|int, 1: float|int} [facturé, dépenses] des courses livrées sur la période */
    private function totaux(int $companyId, CarbonInterface $debut, CarbonInterface $fin): array
    {
        $livrees = fn ($query) => $query
            ->where('courses.company_id', $companyId)
            ->where('courses.statut', 'livree')
            ->whereBetween('courses.livree_at', [$debut->copy()->startOfDay(), $fin->copy()->endOfDay()]);

        return [
            CourseClient::whereHas('course', $livrees)->sum('montant_facture'),
            CourseExpense::whereHas('course', $livrees)->sum('montant'),
        ];
    }

    /** Nombre de courses prévues par jour sur les 7 derniers jours (pour la mini-courbe). */
    private function coursesParJour(int $companyId): array
    {
        $parJour = Course::where('company_id', $companyId)
            ->where('statut', '!=', 'annulee')
            ->whereBetween('date_heure', [today()->subDays(6), today()->endOfDay()])
            ->selectRaw('DATE(date_heure) as jour, COUNT(*) as total')
            ->groupBy('jour')
            ->pluck('total', 'jour');

        return collect(range(6, 0))
            ->map(fn (int $i) => (int) ($parJour[today()->subDays($i)->toDateString()] ?? 0))
            ->all();
    }
}