<?php

namespace App\Filament\Resources\Emplacements\Schemas;

use App\Models\Emplacement;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmplacementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('nom')
                    ->label('Nom')
                    ->placeholder('Ex. Bureau 12')
                    ->required()
                    ->maxLength(255),

                TextInput::make('code')
                    ->label('Code')
                    ->helperText('Facultatif (ex. A-1-12)')
                    ->maxLength(20)
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('type')
                    ->label('Type')
                    ->options(Emplacement::TYPES)
                    ->default('bureau')
                    ->required()
                    ->native(false),

                Select::make('departement_id')
                    ->label('Département')
                    ->placeholder('Aucun')
                    ->relationship('departement', 'nom')
                    ->searchable()
                    ->preload(),
            ]);
    }
}