<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use Filament\Widgets\ChartWidget;

class AbsencesByTypeChart extends ChartWidget
{
    protected ?string $heading = 'Absences par type';

    protected static ?int $sort = 4;

    public ?string $filter = 'year';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'month' => 'Ce mois',
            'year' => 'Cette année',
            'all' => 'Tout',
        ];
    }

    protected function getData(): array
    {
        $query = Absence::query();

        match ($this->filter) {
            'month' => $query->whereBetween('date_absence', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ]),
            'year' => $query->whereYear('date_absence', now()->year),
            default => null,
        };

        $parType = $query
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->pluck('total', 'type');

        return [
            'datasets' => [
                [
                    'label' => 'Absences',
                    'data' => $parType->values()->map(fn ($total) => (int) $total)->all(),
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            // Les absences sans type sont regroupées sous "Non précisé"
            'labels' => $parType->keys()
                ->map(fn ($type) => filled($type) ? ucfirst((string) $type) : 'Non précisé')
                ->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['ticks' => ['precision' => 0]]],
        ];
    }
}