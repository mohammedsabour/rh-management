<?php

namespace App\Filament\Pages;

use App\Models\CommissionInventaire;
use App\Models\Emplacement;
use App\Models\Equipement;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use RuntimeException;

/**
 * Écran de comptage « à l'aveugle » d'une commission.
 * Aucune information sur ce qui est attendu : ni emplacement enregistré, ni reste à compter, ni comparaison.
 * Seul chiffre affiché : le nombre de biens enregistrés par cette commission.
 * Accès réservé aux membres de la commission (l'admin consulte les comptages depuis le récolement).
 */
class ComptageCommission extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'comptage';

    protected string $view = 'filament.pages.comptage-commission';

    #[Url(as: 'commission')]
    public ?int $commissionId = null;

    public string $code = '';

    public ?int $emplacementId = null;   // « Je suis dans » : obligatoire

    public ?string $etat = null;         // état constaté : obligatoire

    public array $historique = [];

    /** Recharge la commission à chaque requête et vérifie l'appartenance (jamais d'objet stocké côté client). */
    public function commission(): CommissionInventaire
    {
        $commission = CommissionInventaire::with(['inventaire', 'membres'])->findOrFail($this->commissionId);

        // Seul le président de la commission effectue le comptage
        abort_unless($commission->estPresident((int) Auth::id()), 403);

        return $commission;
    }

    public function mount(): void
    {
        $commission = $this->commission();

        if (! $commission->est_en_cours) {
            Notification::make()->title('Le comptage de cette commission est terminé.')->warning()->send();

            $this->redirect(MesComptages::getUrl());
        }
    }

    public function getTitle(): string|Htmlable
    {
        // Shield instancie la page sans paramètre pour lister les permissions : pas de commission à ce moment-là
        if (! $this->commissionId) {
            return 'Comptage';
        }

        $c = $this->commission();

        return $c->libelle . ' — ' . $c->inventaire->nom;
    }

    public function scanner(): void
    {
        $resultat = $this->commission()->pointer($this->code, $this->emplacementId, $this->etat, (int) Auth::id());

        $this->code = '';

        if ($resultat['type'] !== 'vide') {
            array_unshift($this->historique, $resultat + ['heure' => now()->format('H:i:s')]);
            $this->historique = array_slice($this->historique, 0, 10);
        }

        $this->js("document.getElementById('code-scan')?.focus()");
    }

    public function annuler(int $ligneId): void
    {
        $this->commission()->annuler($ligneId);

        $this->historique = array_values(array_filter($this->historique, fn ($h) => ($h['ligne_id'] ?? null) !== $ligneId));
    }

    public function statistiques(): array
    {
        return $this->commission()->statistiques();
    }

    public function emplacements(): array
    {
        return Emplacement::with('parent.parent.parent')->get()
            ->mapWithKeys(fn (Emplacement $e) => [$e->id => $e->chemin_complet])
            ->sort()
            ->all();
    }

    public function etats(): array
    {
        return Equipement::ETATS;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('terminer')
                ->label('Terminer le comptage')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('danger')
                ->visible(fn () => $this->commission()->estPresident((int) Auth::id()))
                ->modalHeading('Terminer le comptage de la commission ?')
                ->modalDescription(function () {
                    $s = $this->commission()->statistiques();

                    return new HtmlString(
                        '<strong>Bilan de votre commission</strong><ul style="margin:.5rem 0 .75rem 1.25rem; list-style:disc">'
                        . "<li>Équipements examinés : <strong>{$s['retrouves']}</strong></li>"
                        . "<li>Équipements non retrouvés : <strong>{$s['non_retrouves']}</strong></li>"
                        . "<li>Équipements endommagés ou inutilisables : <strong>{$s['endommages']}</strong></li>"
                        . '</ul>Après validation, plus aucun pointage n\'est possible. Quand les deux commissions ont terminé, '
                        . 'la direction peut rapprocher les résultats.'
                    );
                })
                ->modalSubmitActionLabel('Terminer')
                ->schema([
                    TextInput::make('nb_non_numerotes')
                        ->label('Biens non numérotés ou au numéro illisible')
                        ->numeric()->minValue(0)->default(0),

                    Textarea::make('observations')->label('Autres observations')->rows(3),
                ])
                ->action(function (array $data) {
                    abort_unless($this->commission()->estPresident((int) Auth::id()), 403);

                    try {
                        $this->commission()->terminer($data);
                    } catch (RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title('Comptage terminé')->success()->send();
                    $this->redirect(MesComptages::getUrl());
                }),
        ];
    }
}