<?php

namespace App\Filament\Resources\Equipements\Schemas;

use Filament\Infolists\Components\TextEntry;
use App\Models\Equipement;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Schema;

class EquipementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('photo')
                ->label('Photo')
                ->disk('public')
                ->height(140)
                ->placeholder('Aucune photo'),
 
            TextEntry::make('code_barre')
                ->label('Code-barres')
                ->fontFamily('mono')
                ->copyable()
                ->placeholder('—'),
 
            TextEntry::make('numero_inventaire')
                ->label("N° d'inventaire")
                ->copyable()
                ->placeholder('—'),
 
            TextEntry::make('designation')
                ->label('Désignation'),
 
            TextEntry::make('categorie.nom')
                ->label('Catégorie'),
 
            TextEntry::make('categorie.numero_compte')
                ->label('N° de compte')
                ->placeholder('—'),
 
            TextEntry::make('marque')
                ->label('Marque')
                ->placeholder('—'),
 
            TextEntry::make('modele')
                ->label('Modèle')
                ->placeholder('—'),
 
            TextEntry::make('numero_serie')
                ->label('N° de série')
                ->copyable()
                ->placeholder('—'),
 
            TextEntry::make('etat')
                ->label('État physique')
                ->badge()
                ->formatStateUsing(fn (string $state) => Equipement::ETATS[$state] ?? $state)
                ->color(fn (string $state) => match ($state) {
                    'neuf' => 'success',
                    'bon' => 'info',
                    'use' => 'warning',
                    'degrade' => 'danger',
                    default => 'gray',
                }),
 
            TextEntry::make('statut')
                ->label('Statut')
                ->badge()
                ->formatStateUsing(fn (string $state) => Equipement::STATUTS[$state] ?? $state)
                ->color(fn (string $state) => match ($state) {
                    'en_stock' => 'info',
                    'affecte' => 'success',
                    'en_maintenance' => 'warning',
                    'perdu' => 'danger',
                    default => 'gray',
                }),
 
            TextEntry::make('detenteur')
                ->label('Détenteur actuel')
                ->state(function (Equipement $record) {
                    $affectation = $record->affectationEnCours;
 
                    if (! $affectation) {
                        return null;
                    }
 
                    return $affectation->employe
                        ? trim($affectation->employe->prenom . ' ' . $affectation->employe->nom)
                        : $affectation->departement?->nom;
                })
                ->placeholder('Non affecté'),
 
            TextEntry::make('emplacement.nom')
                ->label('Emplacement')
                ->placeholder('—'),
 
            TextEntry::make('date_acquisition')
                ->label("Date d'acquisition")
                ->date('d/m/Y')
                ->placeholder('—'),
 
            TextEntry::make('prix_acquisition')
                ->label("Prix d'acquisition")
                ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: ' ')
                ->suffix(' DA')
                ->placeholder('—'),
 
            TextEntry::make('reference_facture')
                ->label('Réf. facture / BL')
                ->placeholder('—'),
 
            TextEntry::make('date_fin_garantie')
                ->label('Fin de garantie')
                ->date('d/m/Y')
                ->placeholder('—'),
 
            IconEntry::make('sous_garantie')
                ->label('Sous garantie')
                ->boolean(),
 
            TextEntry::make('notes')
                ->label('Notes')
                ->placeholder('—'),
 
            TextEntry::make('created_at')
                ->label('Créé le')
                ->dateTime('d/m/Y H:i'),
 
            TextEntry::make('updated_at')
                ->label('Modifié le')
                ->dateTime('d/m/Y H:i'),
        ]);
    }
}
