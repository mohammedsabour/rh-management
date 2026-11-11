<?php

namespace App\Filament\Resources\Inventaires\Schemas;

use App\Models\Emplacement;
use App\Models\Inventaire;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InventaireForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nom')
                ->label('Intitulé')
                ->placeholder('Ex. Récolement annuel 2026')
                ->required()
                ->maxLength(255),
 
            DatePicker::make('date_debut')
                ->label('Date de début')
                ->default(today())
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y'),
 
            Select::make('emplacement_id')
                ->label('Périmètre')
                ->placeholder('Tous les emplacements')
                ->helperText('Un bâtiment ou un étage inclut ses sous-emplacements. Non modifiable une fois la liste à contrôler créée.')
                ->options(fn () => Emplacement::with('parent.parent.parent')->get()
                    ->mapWithKeys(fn (Emplacement $e) => [$e->id => $e->chemin_complet])
                    ->sort())
                ->searchable()
                ->disabled(fn (?Inventaire $record) => $record !== null),
 
            Textarea::make('notes')
                ->label('Notes')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }
}
