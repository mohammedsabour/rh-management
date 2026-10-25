<?php

namespace App\Filament\Resources\DemandeConges\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DemandeCongeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('employe_id')
                    ->numeric(),
                TextEntry::make('date_debut')
                    ->date(),
                TextEntry::make('date_fin')
                    ->date(),
                TextEntry::make('statut'),
                TextEntry::make('type')
                    ->placeholder('-'),
                TextEntry::make('commentaire')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
