<?php

namespace App\Filament\Resources\Inventaires\RelationManagers;

use App\Models\Emplacement;
use App\Models\Equipement;
use App\Models\LigneInventaire;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LignesRelationManager extends RelationManager
{
    protected static string $relationship = 'lignes';

    protected static ?string $title = 'Équipements à contrôler';

    // Pas de création / modification libre : les lignes viennent du patrimoine, le pointage se fait par scan
    public function isReadOnly(): bool
    {
        return false;
    }

    private static function ouvertEtAutorise(LigneInventaire $ligne): bool
    {
        return $ligne->inventaire->est_ouvert && Gate::allows('update', $ligne->inventaire);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with([
                'inventaire', 'equipement.emplacement.parent.parent', 'emplacementConstate.parent.parent', 'scannePar',
            ]))
            // À retrouver d'abord
            ->defaultSort(fn (Builder $query) => $query->orderByRaw('scanne_le is not null')->orderBy('id'))
            ->columns([
                TextColumn::make('equipement.numero_inventaire')
                    ->label("N° d'inventaire")
                    ->searchable(),

                TextColumn::make('equipement.designation')
                    ->label('Désignation')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('equipement.code_barre')
                    ->label('Code-barres')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('attendu')
                    ->label('Emplacement enregistré')
                    ->state(fn (LigneInventaire $l) => $l->equipement->emplacement?->chemin_complet ?? '—')
                    ->wrap(),

                TextColumn::make('constate')
                    ->label('Emplacement constaté')
                    ->state(fn (LigneInventaire $l) => $l->emplacementConstate?->chemin_complet)
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('situation')
                    ->label('Situation')
                    ->badge()
                    ->state(fn (LigneInventaire $l) => $l->situation)
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'retrouve' => 'Retrouvé',
                        'deplace' => 'Déplacé',
                        default => 'À retrouver',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'retrouve' => 'success',
                        'deplace' => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('etat_constate')
                    ->label('État constaté')
                    ->formatStateUsing(fn (?string $state) => $state ? (Equipement::ETATS[$state] ?? $state) : null)
                    ->placeholder('—'),

                TextColumn::make('scanne_le')->label('Pointé le')->dateTime('d/m/Y H:i')->placeholder('—'),
                TextColumn::make('scannePar.nom')->label('Pointé par')->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('remarque')->label('Remarque')->placeholder('—')->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('situation')
                    ->label('Situation')
                    ->options([
                        'a_retrouver' => 'À retrouver',
                        'retrouve' => 'Retrouvés',
                        'deplace' => 'Déplacés',
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'a_retrouver' => $query->whereNull('scanne_le'),
                        'retrouve' => $query->whereNotNull('scanne_le'),
                        'deplace' => $query->deplacees(),
                        default => $query,
                    }),
            ])
            ->recordActions([
                // Pour un bien dont l'étiquette est illisible ou absente
                Action::make('pointer')
                    ->label('Pointer')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (LigneInventaire $l) => $l->scanne_le === null && self::ouvertEtAutorise($l))
                    ->modalHeading(fn (LigneInventaire $l) => 'Pointer « ' . $l->equipement->designation . ' »')
                    ->schema([
                        Select::make('emplacement_constate_id')
                            ->label('Emplacement constaté')
                            ->options(fn () => Emplacement::with('parent.parent.parent')->get()
                                ->mapWithKeys(fn (Emplacement $e) => [$e->id => $e->chemin_complet])
                                ->sort())
                            ->default(fn (LigneInventaire $l) => $l->equipement->emplacement_id)
                            ->searchable()
                            ->required(),
                        Select::make('etat_constate')
                            ->label('État constaté')
                            ->options(Equipement::ETATS)
                            ->default(fn (LigneInventaire $l) => $l->equipement->etat)
                            ->required()
                            ->native(false),
                        Textarea::make('remarque')->label('Remarque')->rows(2),
                    ])
                    ->action(function (LigneInventaire $l, array $data) {
                        $l->update($data + ['scanne_le' => now(), 'scanne_par' => Auth::id()]);

                        Notification::make()->success()->title('Équipement pointé')->send();
                    }),

                Action::make('annuler_pointage')
                    ->label('Annuler le pointage')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('gray')
                    ->visible(fn (LigneInventaire $l) => $l->scanne_le !== null && self::ouvertEtAutorise($l))
                    ->requiresConfirmation()
                    ->action(function (LigneInventaire $l) {
                        $l->update([
                            'scanne_le' => null,
                            'scanne_par' => null,
                            'emplacement_constate_id' => null,
                            'etat_constate' => null,
                        ]);

                        Notification::make()->title('Pointage annulé')->send();
                    }),
            ]);
    }
}
