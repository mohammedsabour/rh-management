<?php

namespace App\Filament\Resources\Emplacements\Pages;

use App\Filament\Resources\Emplacements\EmplacementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmplacements extends ListRecords
{
    protected static string $resource = EmplacementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
