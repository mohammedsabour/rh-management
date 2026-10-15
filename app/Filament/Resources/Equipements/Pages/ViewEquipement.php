<?php

namespace App\Filament\Resources\Equipements\Pages;

use App\Filament\Resources\Equipements\EquipementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEquipement extends ViewRecord
{
    protected static string $resource = EquipementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
