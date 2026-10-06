<?php

namespace App\Filament\Resources\Departements\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DepartementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nom')
                    ->required(),
            ]);
    }
}
