<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use Filament\Widgets\ChartWidget;

class CoursesSemaine extends ChartWidget
{
    protected static ?string $heading = 'Courses des 7 derniers jours';

    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return auth()->user()->role === 'responsable';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $jours = collect(range(6, 0))
            ->map(fn ($i) => now()->subDays($i)->startOfDay());

        $parJour = Course::where('company_id', auth()->user()->company_id)
            ->where('statut', '!=', 'annulee')
            ->whereBetween('date_heure', [$jours->first(), now()->endOfDay()])
            ->get(['date_heure'])
            ->countBy(fn ($course) => $course->date_heure->format('Y-m-d'));

        return [
            'datasets' => [
                [
                    'label' => 'Courses',
                    'data' => $jours
                        ->map(fn ($jour) => $parJour->get($jour->format('Y-m-d'), 0))
                        ->all(),
                ],
            ],
            'labels' => $jours->map(fn ($jour) => $jour->format('d/m'))->all(),
        ];
    }
}