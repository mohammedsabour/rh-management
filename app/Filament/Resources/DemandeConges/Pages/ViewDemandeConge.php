<?php

namespace App\Filament\Resources\DemandeConges\Pages;

use App\Filament\Resources\DemandeConges\DemandeCongeResource;
use App\Filament\Resources\DemandeConges\Tables\DemandeCongesTable;
use Filament\Resources\Pages\ViewRecord;

class ViewDemandeConge extends ViewRecord
{
    protected static string $resource = DemandeCongeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DemandeCongesTable::actionApprouver(),
            DemandeCongesTable::actionRefuser(),
            DemandeCongesTable::actionAnnuler(),
        ];
    }
}
