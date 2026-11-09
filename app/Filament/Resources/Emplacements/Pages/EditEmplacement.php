<?php

namespace App\Filament\Resources\Emplacements\Pages;

use App\Filament\Resources\Emplacements\EmplacementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmplacement extends EditRecord
{
    protected static string $resource = EmplacementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
