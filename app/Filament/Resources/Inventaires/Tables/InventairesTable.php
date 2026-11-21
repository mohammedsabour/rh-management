<?php

namespace App\Filament\Resources\Inventaires\Tables;

use App\Filament\Resources\Inventaires\InventaireResource;
use App\Models\Inventaire;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class InventairesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['emplacement.parent.parent', 'commissions']))
            ->defaultSort('date_debut', 'desc')
            ->columns([
                TextColumn::make('nom')
                    ->label('Intitulé')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Inventaire::STATUTS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'comptage' => 'warning',
                        'rapprochement' => 'info',
                        default => 'success',
                    }),

                TextColumn::make('date_debut')->label('Début')->date('d/m/Y')->sortable(),
                TextColumn::make('date_fin')->label('Fin')->date('d/m/Y')->placeholder('En cours'),

                TextColumn::make('perimetre')
                    ->label('Périmètre')
                    ->state(fn (Inventaire $r) => $r->emplacement?->chemin_complet ?? 'Tous les emplacements'),

                TextColumn::make('commissions_etat')
                    ->label('Commissions')
                    ->state(fn (Inventaire $r) => $r->commissions
                        ->map(fn ($c) => $c->code . ' : ' . ($c->est_terminee ? 'terminée' : 'en cours'))
                        ->implode(' · ')),
            ])
            ->filters([
                SelectFilter::make('statut')->label('Statut')->options(Inventaire::STATUTS),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('rapprochement')
                    ->label('Rapprochement')
                    ->icon(Heroicon::OutlinedScale)
                    ->color('info')
                    ->visible(fn (Inventaire $r) => ! $r->en_comptage && Gate::allows('rapprocher', $r))
                    ->url(fn (Inventaire $r) => InventaireResource::getUrl('rapprochement', ['record' => $r])),
            ]);
    }
}
