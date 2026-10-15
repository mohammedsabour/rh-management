<?php

namespace App\Filament\Resources\Equipements\Pages;

use App\Filament\Resources\Equipements\EquipementResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
class ListEquipements extends ListRecords
{
    protected static string $resource = EquipementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('inventaire_comptable')
                ->label('Inventaire comptable')
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('gray')
                ->url(fn () => route('inventaire.comptable'))
                ->openUrlInNewTab(),
            CreateAction::make(),
        ];
    }
}
