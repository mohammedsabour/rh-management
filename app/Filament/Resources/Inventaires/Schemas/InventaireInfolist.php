<?php

namespace App\Filament\Resources\Inventaires\Schemas;

use App\Models\Inventaire;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InventaireInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nom')->label('Intitulé'),
 
            TextEntry::make('statut')
                ->label('Statut')
                ->badge()
                ->formatStateUsing(fn (string $state) => Inventaire::STATUTS[$state] ?? $state)
                ->color(fn (string $state) => $state === 'ouvert' ? 'warning' : 'success'),
 
            TextEntry::make('date_debut')->label('Début')->date('d/m/Y'),
            TextEntry::make('date_fin')->label('Fin')->date('d/m/Y')->placeholder('En cours'),
 
            TextEntry::make('perimetre')
                ->label('Périmètre')
                ->state(fn (Inventaire $r) => $r->emplacement?->chemin_complet ?? 'Tous les emplacements'),
 
            TextEntry::make('createur.nom')->label('Créé par')->placeholder('—'),
 
            TextEntry::make('attendus')->label('Équipements à contrôler')
                ->state(fn (Inventaire $r) => $r->statistiques()['attendus']),
            TextEntry::make('retrouves')->label('Retrouvés')
                ->state(fn (Inventaire $r) => $r->statistiques()['retrouves'])
                ->color('success'),
            TextEntry::make('manquants')->label('À retrouver')
                ->state(fn (Inventaire $r) => $r->statistiques()['manquants'])
                ->color(fn (Inventaire $r) => $r->statistiques()['manquants'] > 0 ? 'danger' : 'success'),
            TextEntry::make('deplaces')->label('Retrouvés hors de leur emplacement')
                ->state(fn (Inventaire $r) => $r->statistiques()['deplaces'])
                ->color(fn (Inventaire $r) => $r->statistiques()['deplaces'] > 0 ? 'warning' : 'gray'),
            TextEntry::make('avancement')->label('Avancement')
                ->state(fn (Inventaire $r) => $r->statistiques()['avancement'] . ' %'),
 
            TextEntry::make('notes')->label('Notes')->placeholder('—'),
        ]);
    }
}
