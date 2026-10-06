<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
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
                    ->searchable(),
                TextColumn::make('prenom')
                    ->searchable(),
                TextColumn::make('email_personnel')
                    ->searchable(),
                TextColumn::make('telephone')
                    ->searchable(),
                TextColumn::make('poste')
                    ->searchable(),
                TextColumn::make('departement_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('date_embauche')
                    ->date()
                    ->sortable(),
                TextColumn::make('role')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}