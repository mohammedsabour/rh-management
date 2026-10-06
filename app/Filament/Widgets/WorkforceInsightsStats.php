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

class WorkforceInsightsStats extends BaseWidget
{
    protected static ?int $sort = 1;

    private function employes(): Builder
    {
        return User::query();
    }

    protected function getStats(): array
    {
        $totalActifs = $this->employes()->where('status', 'actif')->count();
        $totalEmployes = $this->employes()->count();
        $totalInactifs = $this->employes()->where('status', 'inactif')->count();

        // Ancienneté moyenne (en années) des employés actifs ayant une date d'entrée
        $ancienneteMoyenne = $this->employes()
            ->where('status', 'actif')
            ->whereNotNull('date_embauche')
            ->get(['id', 'date_embauche'])
            ->avg(fn (User $employe) => $employe->date_embauche->diffInMonths(now()) / 12);
        $ancienneteMoyenne = $ancienneteMoyenne !== null ? round($ancienneteMoyenne, 1) : 0;

        // Taux de départ : employés inactifs / tous les employés
        $tauxDepart = $totalEmployes > 0
            ? round(($totalInactifs / $totalEmployes) * 100, 1)
            : 0;

        // Contrats à durée déterminée (CDD + CTA) parmi les actifs
        $parContrat = $this->employes()
            ->where('status', 'actif')
            ->selectRaw('type_contrat, count(*) as total')
            ->groupBy('type_contrat')
            ->pluck('total', 'type_contrat');
        $cdd = (int) ($parContrat['CDD'] ?? 0);
        $cta = (int) ($parContrat['CTA'] ?? 0);
        $tauxDeterminee = $totalActifs > 0
            ? round((($cdd + $cta) / $totalActifs) * 100, 1)
            : 0;

        return [
            Stat::make('Ancienneté moyenne', $ancienneteMoyenne . ' ans')
                ->description($totalActifs . ' employés actifs')
                ->descriptionIcon(FilamentIcon::resolve(DemoIconAlias::WIDGETS_WORKFORCE_TENURE) ?? Heroicon::Clock)
                ->color('primary'),
            Stat::make('Taux de départ', $tauxDepart . '%')
                ->description($totalInactifs . ' inactif(s) sur ' . $totalEmployes)
                ->descriptionIcon(FilamentIcon::resolve(DemoIconAlias::WIDGETS_WORKFORCE_TURNOVER) ?? Heroicon::ArrowRightOnRectangle)
                ->color($tauxDepart > 20 ? 'danger' : 'warning'),
            Stat::make('Effectif actif', (string) $totalActifs)
                ->description(Departement::count() . ' département(s)')
                ->descriptionIcon(FilamentIcon::resolve(DemoIconAlias::WIDGETS_WORKFORCE_CAPACITY) ?? Heroicon::UserGroup)
                ->color($totalActifs > 0 ? 'success' : 'gray'),
            Stat::make('Demende de congé', $tauxDeterminee . '%')
                ->description($cdd . ' CDD, ' . $cta . ' CTA')
                ->descriptionIcon(FilamentIcon::resolve(DemoIconAlias::WIDGETS_WORKFORCE_CONTRACTORS) ?? Heroicon::Briefcase)
                ->color('info'),
        ];
    }
}