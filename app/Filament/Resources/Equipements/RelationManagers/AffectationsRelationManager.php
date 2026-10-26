<?php

namespace App\Filament\Resources\Equipements\RelationManagers;

use App\Models\Affectation;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AffectationsRelationManager extends RelationManager
{
    protected static string $relationship = 'affectations';

    protected static ?string $title = 'Historique des affectations';

    // Lecture seule : les affectations se créent via les actions Affecter / Restituer
    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['employe', 'departement', 'createur']))
            ->defaultSort('date_debut', 'desc')
            ->columns([
                TextColumn::make('detenteur')
                    ->label('Détenteur')
                    ->state(fn (Affectation $record) => $record->employe
                        ? trim($record->employe->prenom . ' ' . $record->employe->nom)
                        : ($record->departement?->nom ?? '—')),

                TextColumn::make('date_debut')
                    ->label('Du')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('date_fin')
                    ->label('Au')
                    ->date('d/m/Y')
                    ->placeholder('En cours')
                    ->badge(fn (Affectation $record) => $record->date_fin === null)
                    ->color('success'),

                TextColumn::make('createur.nom')
                    ->label('Saisi par')
                    ->placeholder('—'),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->placeholder('—')
                    ->wrap(),
            ]);
    }
}