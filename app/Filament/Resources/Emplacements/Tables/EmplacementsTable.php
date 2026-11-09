<?php

namespace App\Filament\Resources\Emplacements\Tables;

use App\Models\Emplacement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmplacementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('parent.parent.parent'))
            ->columns([
                TextColumn::make('nom')
                    ->label('Emplacement')
                    ->description(fn (Emplacement $record) => $record->parent_id ? $record->chemin_complet : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Emplacement::TYPES[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Code')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('departement.nom')
                    ->label('Département')
                    ->placeholder('—')
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('equipements_count')
                    ->label('Équipements')
                    ->counts('equipements')
                    ->sortable(),
            ])
            ->defaultSort('nom')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(Emplacement::TYPES),

                SelectFilter::make('departement_id')
                    ->label('Département')
                    ->relationship('departement', 'nom'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}