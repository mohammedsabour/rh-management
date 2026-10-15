<?php

namespace App\Filament\Resources\Equipements\Pages;

use App\Filament\Resources\Equipements\EquipementResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipement extends EditRecord
{
    protected static string $resource = EquipementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
