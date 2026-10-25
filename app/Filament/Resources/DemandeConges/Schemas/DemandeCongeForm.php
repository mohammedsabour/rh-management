<?php

namespace App\Filament\Resources\DemandeConges\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DemandeCongeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employe_id')
                    ->relationship('employe', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('date_debut')
                    ->label('Date de début')
                    ->minDate(Carbon::tomorrow())
                    ->live()
                    ->required(),
                DatePicker::make('date_fin')
                    ->label('Date de fin')
                    ->required()
                    ->minDate(fn (Get $get) => $get('date_debut') 
                        ? Carbon::parse($get('date_debut')) 
                        : Carbon::tomorrow()
                    ),
                Select::make('statut')
                    ->options(['en_attente' => 'En attente', 'approuve' => 'Approuve', 'refuse' => 'Refuse'])          
                    ->required()
                    ->default('en_attente'),
                TextInput::make('type'),
                Textarea::make('commentaire')
                    ->columnSpanFull(),
            ]);
    }
}
