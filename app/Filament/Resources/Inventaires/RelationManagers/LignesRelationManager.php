<?php

namespace App\Filament\Resources\Inventaires\RelationManagers;

use App\Models\Equipement;
use App\Models\LigneInventaire;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Détail des comptages des deux commissions : réservé à l'admin et au créateur du récolement.
 * Les autres utilisateurs (même avec le droit Inventaire) n'y ont jamais accès.
 */
class LignesRelationManager extends RelationManager
{
    protected static string $relationship = 'lignes';

    protected static ?string $title = 'Détail des comptages';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = Auth::user();

        return $user && ($user->hasRole('admin') || $ownerRecord->cree_par === $user->id);
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with([
                'commission', 'equipement', 'emplacementAttendu.parent.parent', 'emplacementConstate.parent.parent', 'scannePar',
            ]))
            ->defaultSort('id')
            ->columns([
                TextColumn::make('commission.code')->label('Commission')->badge(),

                TextColumn::make('equipement.numero_inventaire')->label("N° d'inventaire")->searchable(),

                TextColumn::make('equipement.designation')->label('Désignation')->searchable()->wrap(),

                TextColumn::make('attendu')
                    ->label('Emplacement attendu')
                    ->state(fn (LigneInventaire $l) => $l->emplacementAttendu?->chemin_complet ?? '—')
                    ->wrap(),

                IconColumn::make('retrouve')->label('Retrouvé')->boolean()
                    ->state(fn (LigneInventaire $l) => $l->scanne_le !== null),

                TextColumn::make('constate')
                    ->label('Emplacement constaté')
                    ->state(fn (LigneInventaire $l) => $l->emplacementConstate?->chemin_complet ?? '—')
                    ->wrap(),

                TextColumn::make('etat_constate')
                    ->label('État constaté')
                    ->formatStateUsing(fn (?string $state) => Equipement::ETATS[$state] ?? '—'),

                IconColumn::make('hors_perimetre')->label('Hors périmètre')->boolean(),

                TextColumn::make('scannePar.nom')->label('Pointé par')->placeholder('—'),
                TextColumn::make('scanne_le')->label('Le')->dateTime('d/m/Y H:i')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('commission_id')
                    ->label('Commission')
                    ->options(fn () => $this->getOwnerRecord()->commissions()->pluck('code', 'id')->map(fn ($c) => "Commission {$c}")->all()),

                TernaryFilter::make('retrouve')
                    ->label('Retrouvé')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('scanne_le'),
                        false: fn ($q) => $q->whereNull('scanne_le'),
                    ),
            ]);
    }
}