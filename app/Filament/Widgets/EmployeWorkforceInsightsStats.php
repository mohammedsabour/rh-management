<?php

namespace App\Filament\Widgets;

use App\Filament\DemoIconAlias;
use App\Models\Departement;
use App\Models\User;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class EmployeWorkforceInsightsStats extends BaseWidget
{
    protected static ?int $sort = 1;

    private function employes(): Builder
    {
        return User::query();
    }

    protected function getStats(): array
    {
        $totalActifs = $this->employes()->where('is_active', 'true')->count();
        $totalEmployes = $this->employes()->count();
        $totalInactifs = $this->employes()->where('is_active', 'false')->count();

        // Contrats à durée déterminée (CDD + CTA) parmi les actifs
        $parContrat = $this->employes()
            ->where('is_active', 'true')
            ->selectRaw('type_contrat, count(*) as total')
            ->groupBy('type_contrat')
            ->pluck('total', 'type_contrat');
        $cdd = (int) ($parContrat['CDD'] ?? 0);
        $cta = (int) ($parContrat['CTA'] ?? 0);
        $tauxDeterminee = $totalActifs > 0
            ? round((($cdd + $cta) / $totalActifs) * 100, 1)
            : 0;

        return [
            Stat::make('Jours des absence par mois', (string) $totalActifs)
                ->description(Departement::count() . ' département(s)')
                ->descriptionIcon(FilamentIcon::resolve(DemoIconAlias::WIDGETS_WORKFORCE_CAPACITY) ?? Heroicon::UserGroup)
                ->color($totalActifs > 0 ? 'success' : 'gray'),
            Stat::make('Jours restants de congé', $tauxDeterminee . ' jr')
                ->description($cdd . ' CDD, ' . $cta . ' CTA')
                ->descriptionIcon(FilamentIcon::resolve(DemoIconAlias::WIDGETS_WORKFORCE_CONTRACTORS) ?? Heroicon::Briefcase)
                ->color('info'),
        ];
    }
}