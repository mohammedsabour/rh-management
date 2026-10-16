<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ActionGroup;
use Filament\Tables\Table;

class UsersTable
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
                TextColumn::make('nom')
                    ->formatStateUsing(fn ($record) => "{$record->nom} {$record->prenom}")
                    ->searchable(['nom', 'prenom'])
                    ->sortable()
                    ->weight(FontWeight::Medium),
                TextColumn::make('telephone')
                    ->searchable(),
                TextColumn::make('poste')
                    ->searchable(),
                TextColumn::make('departement.nom')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('No department'),
                TextColumn::make('role')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('departement')
                    ->relationship('departement', 'nom'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}