<?php

namespace App\Filament\Resources\Inventaires\Pages;

use App\Filament\Resources\Inventaires\InventaireResource;
use App\Models\Emplacement;
use App\Models\Equipement;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Écran de pointage : une douchette USB tape le code puis « Entrée » dans le champ, qui reste toujours actif.
 */
class ScannerInventaire extends Page
{
    use InteractsWithRecord;

    protected static string $resource = InventaireResource::class;

    protected string $view = 'filament.resources.inventaires.pages.scanner-inventaire';

    public string $code = '';

    public ?int $emplacementId = null;   // « Je suis dans : » (emplacement constaté pour les prochains scans)

    public ?string $etat = null;         // état constaté, facultatif

    public array $historique = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(Gate::allows('update', $this->record), 403);

        if (! $this->record->est_ouvert) {
            Notification::make()->title('Ce récolement est clôturé.')->warning()->send();

            $this->redirect(InventaireResource::getUrl('view', ['record' => $this->record]));
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'Pointage — ' . $this->record->nom;
    }

    public function scanner(): void
    {
        $resultat = $this->record->pointer($this->code, $this->emplacementId, $this->etat ?: null, (int) Auth::id());

        $this->code = '';

        if ($resultat['type'] !== 'vide') {
            array_unshift($this->historique, $resultat + ['heure' => now()->format('H:i:s')]);
            $this->historique = array_slice($this->historique, 0, 12);
        }

        // Le champ garde le focus : le prochain scan part tout de suite
        $this->js("document.getElementById('code-scan')?.focus()");
    }

    /** Liste « Je suis dans : » */
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
}
