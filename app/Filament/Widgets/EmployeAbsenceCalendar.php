<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use App\Models\DemandeConge;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Calendrier (mois / semaine) des absences et congés approuvés de l'employé connecté.
 * Sans dépendance externe : Livewire + une vue Blade.
 */
class EmployeAbsenceCalendar extends Widget
{
    protected static ?int $sort = 2;

    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.employe-absence-calendar';

    public string $vue = 'mois';      // mois | semaine

    public string $date = '';         // jour de référence (Y-m-d)

    private const COULEURS = [
        'non_justifiee' => ['label' => 'Non justifiée', 'couleur' => '#dc2626'],
        'maladie' => ['label' => 'Maladie', 'couleur' => '#eab308'],
        'conge' => ['label' => 'Congé', 'couleur' => '#16a34a'],
        'personnel' => ['label' => 'Personnel', 'couleur' => '#3b82f6'],
    ];

    public function mount(): void
    {
        $this->date = today()->toDateString();
    }

    public function precedent(): void
    {
        $this->date = $this->reference()->{$this->vue === 'semaine' ? 'subWeek' : 'subMonthNoOverflow'}()->toDateString();
    }

    public function suivant(): void
    {
        $this->date = $this->reference()->{$this->vue === 'semaine' ? 'addWeek' : 'addMonthNoOverflow'}()->toDateString();
    }

    public function aujourdhui(): void
    {
        $this->date = today()->toDateString();
    }

    public function choisirVue(string $vue): void
    {
        $this->vue = $vue === 'semaine' ? 'semaine' : 'mois';
    }

    private function reference(): Carbon
    {
        return Carbon::parse($this->date ?: today());
    }

    /** Catégorie d'affichage d'une absence d'après son type (texte libre en base). */
    private function categorie(?string $type): string
    {
        $t = Str::of((string) $type)->ascii()->lower();

        return match (true) {
            $t->contains('maladie') => 'maladie',
            $t->contains('conge') => 'conge',
            $t->contains('personnel') => 'personnel',
            default => 'non_justifiee',   // type vide ou autre : traité comme non justifiée
        };
    }

    protected function getViewData(): array
    {
        $ref = $this->reference();
        $semaine = $this->vue === 'semaine';

        $debut = $semaine ? $ref->copy()->startOfWeek(Carbon::MONDAY) : $ref->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $fin = $semaine ? $ref->copy()->endOfWeek(Carbon::SUNDAY) : $ref->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $employeId = Auth::id();
        $evenements = [];   // 'Y-m-d' => [ ['label', 'couleur'], ... ]

        // Absences
        Absence::where('employe_id', $employeId)
            ->whereBetween('date_absence', [$debut->toDateString(), $fin->toDateString()])
            ->get()
            ->each(function (Absence $a) use (&$evenements) {
                $cat = self::COULEURS[$this->categorie($a->type)];
                $evenements[Carbon::parse($a->date_absence)->toDateString()][] = $cat;
            });

        // Congés approuvés (jours ouvrés seulement : vendredi et samedi exclus)
        DemandeConge::where('employe_id', $employeId)
            ->where('statut', 'approuve')
            ->whereDate('date_debut', '<=', $fin)
            ->whereDate('date_fin', '>=', $debut)
            ->get()
            ->each(function (DemandeConge $d) use (&$evenements, $debut, $fin) {
                $cat = self::COULEURS[Str::contains(Str::lower($d->type), 'maladie') ? 'maladie' : 'conge'];
                $de = Carbon::parse($d->date_debut)->max($debut);
                $a = Carbon::parse($d->date_fin)->min($fin);

                foreach (CarbonPeriod::create($de, $a) as $jour) {
                    if (! in_array($jour->dayOfWeek, DemandeConge::JOURS_WEEKEND, true)) {
                        $evenements[$jour->toDateString()][] = $cat;
                    }
                }
            });

        $jours = [];
        foreach (CarbonPeriod::create($debut, $fin) as $jour) {
            $jours[] = [
                'date' => $jour,
                'dansMois' => $semaine || $jour->month === $ref->month,
                'aujourdhui' => $jour->isToday(),
                'weekend' => in_array($jour->dayOfWeek, DemandeConge::JOURS_WEEKEND, true),
                'evenements' => $evenements[$jour->toDateString()] ?? [],
            ];
        }

        $titre = $semaine
            ? $debut->locale('fr')->translatedFormat('j M') . ' – ' . $fin->locale('fr')->translatedFormat('j M Y')
            : $ref->copy()->locale('fr')->translatedFormat('F Y');

        return [
            'jours' => $jours,
            'titre' => $titre,
            'legende' => array_values(self::COULEURS),
            'joursSemaine' => ['lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.', 'dim.'],
        ];
    }
}