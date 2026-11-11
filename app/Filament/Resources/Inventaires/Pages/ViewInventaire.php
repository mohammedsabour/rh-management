<?php

namespace App\Filament\Resources\Inventaires\Pages;

use App\Filament\Resources\Inventaires\InventaireResource;
use App\Models\Inventaire;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

class ViewInventaire extends ViewRecord
{
    protected static string $resource = InventaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scanner')
                ->label('Pointer (code-barres)')
                ->icon(Heroicon::OutlinedQrCode)
                ->color('success')
                ->visible(fn (Inventaire $record) => $record->est_ouvert && Gate::allows('update', $record))
                ->url(fn (Inventaire $record) => InventaireResource::getUrl('scanner', ['record' => $record])),
 
            Action::make('cloturer')
                ->label('Clôturer')
                ->icon(Heroicon::OutlinedLockClosed)
                ->color('danger')
                ->visible(fn (Inventaire $record) => $record->est_ouvert && Gate::allows('update', $record))
                ->modalHeading('Clôturer ce récolement ?')
                ->modalDescription(function (Inventaire $record) {
                    $s = $record->statistiques();
 
                    return "{$s['manquants']} équipement(s) non retrouvé(s) et {$s['deplaces']} retrouvé(s) hors de leur emplacement. "
                        . 'Après la clôture, plus aucun pointage n\'est possible.';
                })
                ->modalSubmitActionLabel('Clôturer')
                ->schema([
                    Toggle::make('appliquer')
                        ->label("Mettre à jour l'emplacement des biens retrouvés ailleurs")
                        ->helperText("Les équipements déplacés prennent l'emplacement constaté. Les biens non retrouvés ne sont pas modifiés.")
                        ->default(false),
                ])
                ->action(function (Inventaire $record, array $data) {
                    try {
                        $record->cloturer((bool) ($data['appliquer'] ?? false));
                    } catch (RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
 
                        return;
                    }
 
                    Notification::make()->title('Récolement clôturé')->success()->send();
                }),

            EditAction::make()
                ->visible(fn (Inventaire $record) => $record->est_ouvert),
        ];
    }
}
