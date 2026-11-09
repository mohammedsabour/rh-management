<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use App\Models\DemandeConge;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class EmployeWorkforceInsightsStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        /** @var User $emp */
        $emp = Auth::user();

        // Absences du mois en cours (une ligne = un jour d'absence)
        $absencesMois = Absence::where('employe_id', $emp->id)
            ->whereBetween('date_absence', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->count();
        $absencesAnnee = Absence::where('employe_id', $emp->id)
            ->whereYear('date_absence', now()->year)
            ->count();

        // Demandes de congé
        $demandes = DemandeConge::where('employe_id', $emp->id)
            ->selectRaw('statut, count(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');
        $totalDemandes = (int) $demandes->sum();
        $enAttente = (int) ($demandes['en_attente'] ?? 0);
        $approuvees = (int) ($demandes['approuve'] ?? 0);

        // Jours de congé réellement pris cette année (demandes approuvées déjà commencées)
        $joursPris = (int) DemandeConge::where('employe_id', $emp->id)
            ->where('statut', 'approuve')
            ->whereYear('date_debut', now()->year)
            ->where('date_debut', '<=', now()->toDateString())
            ->sum('nombre_jours');

        // Prochain congé approuvé
        $prochain = DemandeConge::where('employe_id', $emp->id)
            ->where('statut', 'approuve')
            ->whereDate('date_fin', '>=', today())
            ->orderBy('date_debut')
            ->first();

        return [
            Stat::make('Jours d\'absence ce mois', (string) $absencesMois)
                ->description($absencesAnnee . ' sur l\'année ' . now()->year)
                ->descriptionIcon(Heroicon::CalendarDays)
                ->color($absencesMois > 0 ? 'warning' : 'success'),

            Stat::make('Demandes de congé', (string) $totalDemandes)
                ->description($enAttente . ' en attente, ' . $approuvees . ' approuvée(s)')
                ->descriptionIcon(Heroicon::DocumentText)
                ->color($enAttente > 0 ? 'warning' : 'info'),

            Stat::make('Congés pris cette année', $joursPris . ' jr')
                ->description('Jours ouvrés, demandes approuvées')
                ->descriptionIcon(Heroicon::Sun)
                ->color('success'),

            Stat::make('Prochain congé', $prochain ? $prochain->date_debut->format('d/m/Y') : '—')
                ->description($prochain ? 'au ' . $prochain->date_fin->format('d/m/Y') : 'Aucun congé approuvé à venir')
                ->descriptionIcon(Heroicon::Calendar)
                ->color($prochain ? 'info' : 'gray'),
        ];
    }
}