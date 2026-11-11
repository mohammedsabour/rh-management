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
            ->modifyQueryUsing(fn ($query) => $query
                ->with('emplacement.parent.parent')
                ->withCount([
                    'lignes',
                    'lignes as lignes_retrouvees_count' => fn ($q) => $q->whereNotNull('scanne_le'),
                ]))
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
                    ->color(fn (string $state) => $state === 'ouvert' ? 'warning' : 'success'),
 
                TextColumn::make('date_debut')->label('Début')->date('d/m/Y')->sortable(),
                TextColumn::make('date_fin')->label('Fin')->date('d/m/Y')->placeholder('En cours'),
 
                TextColumn::make('perimetre')
                    ->label('Périmètre')
                    ->state(fn (Inventaire $r) => $r->emplacement?->chemin_complet ?? 'Tous les emplacements'),
 
                TextColumn::make('progression')
                    ->label('Retrouvés')
                    ->state(fn (Inventaire $r) => $r->lignes_retrouvees_count . ' / ' . $r->lignes_count),
            ])
            ->filters([
                SelectFilter::make('statut')->label('Statut')->options(Inventaire::STATUTS),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('scanner')
                    ->label('Pointer')
                    ->icon(Heroicon::OutlinedQrCode)
                    ->color('success')
                    ->visible(fn (Inventaire $r) => $r->est_ouvert && Gate::allows('update', $r))
                    ->url(fn (Inventaire $r) => InventaireResource::getUrl('scanner', ['record' => $r])),
            ]);
    }
}
