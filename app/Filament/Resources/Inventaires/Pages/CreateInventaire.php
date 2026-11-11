<?php

namespace App\Filament\Resources\Inventaires\Pages;

use App\Filament\Resources\Inventaires\InventaireResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInventaire extends CreateRecord
{
    protected static string $resource = InventaireResource::class;

    protected static bool $canCreateAnother = false;
 
    // Statut et auteur sont décidés ici, jamais par le formulaire
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + [
            'statut' => 'ouvert',
            'cree_par' => Auth::id(),
        ];
    }

    // liste des équipements à contrôler
    protected function afterCreate(): void
    {
        $nombre = $this->record->genererLignes();
 
        $notification = Notification::make()->title($nombre . ' équipement(s) à contrôler');
 
        $nombre > 0
            ? $notification->success()->send()
            : $notification->warning()->body('Aucun équipement dans ce périmètre.')->send();
    }
 
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
 
    protected function getCreatedNotificationTitle(): ?string
    {
        return null;
    }
}
