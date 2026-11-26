<?php

namespace App\Filament\Resources\Inventaires\Pages;

use App\Filament\Resources\Inventaires\InventaireResource;
use App\Models\Emplacement;
use App\Models\Equipement;
use App\Models\Inventaire;
use App\Models\RapprochementLigne;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

/**
 * Rapprochement A / B : comparaison en lecture seule, puis décision GLOBALE à la clôture
 * (admin ou créateur du récolement). Le visa du directeur se fait sur le PV papier.
 */
class Rapprochement extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = InventaireResource::class;

    protected string $view = 'filament.resources.inventaires.pages.rapprochement';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(Gate::allows('rapprocher', $this->record), 403);

        if ($this->record->en_comptage) {
            Notification::make()->title('Les deux commissions n\'ont pas encore terminé leur comptage.')->warning()->send();

            $this->redirect(InventaireResource::getUrl('view', ['record' => $this->record]));
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'Rapprochement — ' . $this->record->nom;
    }

    public function getSubheading(): string|Htmlable|null
    {
        if ($this->record->est_cloture) {
            return 'Récolement clôturé.';
        }

        $n = $this->record->nombreEcarts();

        return $n > 0
            ? "{$n} écart(s) entre les deux commissions : examinez-les, puis clôturez en choisissant le résultat du rapprochement."
            : 'Aucun écart entre les deux commissions.';
    }

    protected function getHeaderActions(): array
    {
        $commissions = $this->record->commissions;

        return [
            Action::make('pv_a')->label('PV commission A')->icon(Heroicon::OutlinedPrinter)->color('gray')
                ->url(fn () => route('recolement.pv-commission', $commissions->firstWhere('code', 'A')))
                ->openUrlInNewTab(),

            Action::make('pv_b')->label('PV commission B')->icon(Heroicon::OutlinedPrinter)->color('gray')
                ->url(fn () => route('recolement.pv-commission', $commissions->firstWhere('code', 'B')))
                ->openUrlInNewTab(),

            Action::make('pv_final')
                ->label(fn () => $this->record->est_cloture ? 'PV final unifié' : 'Aperçu du PV final')
                ->icon(Heroicon::OutlinedDocumentText)
                ->color(fn () => $this->record->est_cloture ? 'info' : 'gray')
                ->url(fn () => route('recolement.pv-final', $this->record))
                ->openUrlInNewTab(),

            Action::make('cloturer')
                ->label('Clôturer')
                ->icon(Heroicon::OutlinedLockClosed)
                ->color('danger')
                ->visible(fn () => ! $this->record->est_cloture && Gate::allows('rapprocher', $this->record))
                ->modalHeading('Clôturer le récolement')
                ->modalDescription('Le résultat et le détail figurent sur le PV final. Le visa du directeur se fait sur le PV imprimé. Après la clôture, plus aucune modification n\'est possible.')
                ->modalSubmitActionLabel('Clôturer')
                ->schema([
                    Radio::make('resultat_rapprochement')
                        ->label('Résultat du rapprochement')
                        ->options(Inventaire::RESULTATS)
                        ->required(),

                    Textarea::make('details_rapprochement')->label('Détail des écarts et réserves')->rows(3),

                    Toggle::make('appliquer')
                        ->label('Mettre à jour les équipements retrouvés (emplacement et état décidés)')
                        ->helperText('Seuls les biens constatés à l\'identique par les deux commissions sont mis à jour. Les autres ne sont jamais modifiés automatiquement.')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    abort_unless(Gate::allows('rapprocher', $this->record), 403);

                    try {
                        $this->record->cloturer($data, (bool) ($data['appliquer'] ?? false));
                    } catch (RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title('Récolement clôturé')->success()->send();
                    $this->redirect(InventaireResource::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }

    public function table(Table $table): Table
    {
        $lieu = fn (?Emplacement $e) => $e?->chemin_complet ?? '—';

        return $table
            ->query(RapprochementLigne::query()
                ->where('inventaire_id', $this->record->id)
                ->with(['equipement', 'aEmplacement.parent.parent', 'bEmplacement.parent.parent']))
            ->defaultSort('id')
            ->columns([
                TextColumn::make('equipement.numero_inventaire')->label("N° d'inventaire")
                    ->searchable()->sortable(),

                TextColumn::make('equipement.designation')->label('Désignation')->searchable()->wrap(),

                TextColumn::make('resultat_a')
                    ->label('Commission A')
                    ->state(fn (RapprochementLigne $l) => $l->a_trouve
                        ? $lieu($l->aEmplacement) . ' · ' . (Equipement::ETATS[$l->a_etat] ?? '')
                        : 'Non retrouvé')
                    ->color(fn (RapprochementLigne $l) => $l->a_trouve ? null : 'danger')
                    ->wrap(),

                TextColumn::make('resultat_b')
                    ->label('Commission B')
                    ->state(fn (RapprochementLigne $l) => $l->b_trouve
                        ? $lieu($l->bEmplacement) . ' · ' . (Equipement::ETATS[$l->b_etat] ?? '')
                        : 'Non retrouvé')
                    ->color(fn (RapprochementLigne $l) => $l->b_trouve ? null : 'danger')
                    ->wrap(),

                TextColumn::make('concordance')
                    ->label('Concordance')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => RapprochementLigne::CONCORDANCES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'concordant' => 'success',
                        'introuvable' => 'gray',
                        'un_seul' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('concordance')->label('Concordance')->options(RapprochementLigne::CONCORDANCES),
            ]);
    }
}