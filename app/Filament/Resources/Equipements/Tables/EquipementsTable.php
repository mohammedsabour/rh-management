<?php

namespace App\Filament\Resources\Equipements\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Table;

class EquipementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->disk('public')
                    ->visibility('public')
                    ->circular()
                    ->size(40),
                TextColumn::make('numero_inventaire')
                    ->searchable(),
                TextColumn::make('designation')
                    ->searchable(),
                TextColumn::make('modele')
                    ->searchable(),
                TextColumn::make('etat')
                    ->searchable(),
                TextColumn::make('statut')
                    ->searchable(),
                TextColumn::make('emplacement.nom')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('No position'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('etiquette')
                        ->label('Étiquette')
                        ->icon(Heroicon::OutlinedQrCode)
                        ->url(fn ($record) => route('inventaire.etiquettes', ['ids' => $record->id]))
                        ->openUrlInNewTab(),
                    ViewAction::make(),
                    EditAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('etiquettes')
                        ->label('Imprimer les étiquettes')
                        ->icon(Heroicon::OutlinedPrinter)
                        ->action(fn (Collection $records) => redirect()->route('inventaire.etiquettes', [
                            'ids' => $records->pluck('id')->implode(','),
                        ]))
                        ->openUrlInNewTab()
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
