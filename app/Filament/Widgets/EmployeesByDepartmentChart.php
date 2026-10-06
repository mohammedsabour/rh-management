<?php

namespace App\Filament\Widgets;

use App\Models\Departement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Str;

class EmployeesByDepartmentChart extends ChartWidget
{
    protected ?string $heading = 'Employés par département';

    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Employés actifs seulement ; retirez le where pour compter tout le monde
        $departements = Departement::query()
            ->withCount(['users' => fn ($query) => $query->where('status', 'actif')])
            ->orderBy('nom')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Employés',
                    'data' => $departements->pluck('users_count')->all(),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $departements->pluck('nom')->map(fn (string $nom) => Str::limit($nom, 20))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['ticks' => ['precision' => 0]]], // nombres entiers uniquement
        ];
    }
}