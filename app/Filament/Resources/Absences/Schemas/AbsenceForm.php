<?php

namespace App\Filament\Resources\Absences\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AbsenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date_absence')
                
                    ->required(),
                Select::make('type')
                    ->options(['maladie' => 'Maladie', 'congé' => 'Congé', 'personnel' => 'Personnel'])
                    ->default('maladie')
                    ->required(),
                Select::make('employe_id')
                    ->relationship('employe', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('justificatif')
                    ->options(['oui' => 'Oui', 'non' => 'Non'])
                    ->default('non')
                    ->required(),
            ]);
    }
}
