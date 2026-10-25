<?php

namespace App\Filament\Resources\DemandeConges\Pages;

use App\Filament\Resources\DemandeConges\DemandeCongeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDemandeConge extends EditRecord
{
    protected static string $resource = DemandeCongeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
