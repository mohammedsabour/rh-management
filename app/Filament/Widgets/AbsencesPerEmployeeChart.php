<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AbsencesPerEmployeeChart extends ChartWidget
{
    protected ?string $heading = 'Absences par employé (12 derniers mois)';

    protected static ?int $sort = 3;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        // Les 12 derniers mois, du plus ancien au mois courant
        $mois = collect(range(11, 0))
            ->map(fn (int $moisPasses) => Carbon::now()->subMonths($moisPasses)->startOfMonth());

        // Nombre d'absences par mois ("2026-10" => 7)
        $absencesParMois = Absence::query()
            ->where('date_absence', '>=', $mois->first()->toDateString())
            ->get(['id', 'date_absence'])
            ->groupBy(fn (Absence $absence) => substr((string) $absence->date_absence, 0, 7))
            ->map(fn ($groupe) => $groupe->count());

        $employes = User::query()
            ->where('status', 'actif')
            ->get(['id', 'date_embauche']);

        $valeurs = [];
        $labels = [];

        foreach ($mois as $debut) {
            $fin = $debut->copy()->endOfMonth();

            // Effectif du mois : employés actifs déjà embauchés à la fin de ce mois
            $effectif = $employes
                ->filter(fn (User $employe) => $employe->date_embauche === null || $employe->date_embauche->lte($fin))
                ->count();

            $total = (int) ($absencesParMois[$debut->format('Y-m')] ?? 0);

            $valeurs[] = $effectif > 0 ? round($total / $effectif, 2) : 0;
            $labels[] = $debut->copy()->locale('fr')->translatedFormat('M Y');
        }

        $moyenne = round((float) collect($valeurs)->avg(), 2);

        return [
            'datasets' => [
                [
                    'label' => 'Absences par employé',
                    'data' => $valeurs,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => 'start',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Moyenne sur 12 mois',
                    'data' => array_fill(0, count($valeurs), $moyenne),
                    'borderColor' => '#22c55e',
                    'borderDash' => [5, 5],
                    'pointRadius' => 0,
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => ['y' => ['beginAtZero' => true]],
        ];
    }
}