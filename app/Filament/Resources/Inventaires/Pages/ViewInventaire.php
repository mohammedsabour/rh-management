<?php

namespace App\Filament\Resources\Inventaires\Pages;

use App\Filament\Resources\Inventaires\InventaireResource;
use App\Models\Inventaire;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class ViewInventaire extends ViewRecord
{
    protected static string $resource = InventaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rapprochement')
                ->label('Rapprochement')
                ->icon(Heroicon::OutlinedScale)
                ->color('info')
                ->visible(fn (Inventaire $record) => ! $record->en_comptage && Gate::allows('rapprocher', $record))
                ->url(fn (Inventaire $record) => InventaireResource::getUrl('rapprochement', ['record' => $record])),

            EditAction::make()
                ->visible(fn (Inventaire $record) => ! $record->est_cloture),
        ];
    }
}
