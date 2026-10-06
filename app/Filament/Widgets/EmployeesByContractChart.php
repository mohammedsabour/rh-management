<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class EmployeesByContractChart extends ChartWidget
{
    protected ?string $heading = 'Employés par type de contrat';

    protected static ?int $sort = 3;

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $parContrat = User::query()
            ->where('status', 'actif')
            ->selectRaw('type_contrat, count(*) as total')
            ->groupBy('type_contrat')
            ->pluck('total', 'type_contrat');

        $labels = ['CDI', 'CDD', 'CTA'];
        $data = array_map(fn (string $type) => (int) ($parContrat[$type] ?? 0), $labels);
        $colors = ['#10b981', '#f59e0b', '#3b82f6'];

        // Employés dont le type de contrat n'est pas renseigné
        $nonPrecise = (int) ($parContrat[''] ?? 0);
        if ($nonPrecise > 0) {
            $labels[] = 'Non précisé';
            $data[] = $nonPrecise;
            $colors[] = '#9ca3af';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Employés',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}