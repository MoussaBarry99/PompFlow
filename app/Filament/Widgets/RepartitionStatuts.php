<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use Filament\Widgets\ChartWidget;

class RepartitionStatuts extends ChartWidget
{
    protected static ?string $heading = 'Statuts des courses du mois';

    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return auth()->user()->role === 'responsable';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $couleurs = [
            'Planifiée' => '#9ca3af',
            'En cours' => '#3b82f6',
            'En retard' => '#ef4444',
            'Livrée' => '#22c55e',
            'Livrée en retard' => '#f59e0b',
            'Annulée' => '#6b7280',
        ];

        $parStatut = Course::where('company_id', auth()->user()->company_id)
            ->whereBetween('date_heure', [now()->startOfMonth(), now()->endOfMonth()])
            ->get()
            ->countBy(fn ($course) => $course->statut_affiche);

        return [
            'datasets' => [
                [
                    'data' => collect($couleurs)
                        ->keys()
                        ->map(fn ($statut) => $parStatut->get($statut, 0))
                        ->all(),
                    'backgroundColor' => array_values($couleurs),
                ],
            ],
            'labels' => array_keys($couleurs),
        ];
    }
}