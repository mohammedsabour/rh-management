<?php

namespace App\Filament\Resources\DemandeConges\Pages;

use App\Filament\Resources\DemandeConges\DemandeCongeResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\DemandeConge;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateDemandeConge extends CreateRecord
{
    protected static string $resource = DemandeCongeResource::class;

    protected static bool $canCreateAnother = false;
 
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
 
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Votre demande a été envoyée.';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var User $user */
        $user = Auth::user();
 
        $jours = DemandeConge::compterJoursOuvres($data['date_debut'], $data['date_fin']);
 
        if ($jours < 1) {
            $this->refuserSaisie('La période choisie ne contient aucun jour ouvré.');
        }
 
        $chevauche = DemandeConge::where('employe_id', $user->id)
            ->whereIn('statut', ['en_attente', 'approuve'])
            ->whereDate('date_debut', '<=', $data['date_fin'])
            ->whereDate('date_fin', '>=', $data['date_debut'])
            ->exists();
 
        if ($chevauche) {
            $this->refuserSaisie('Vous avez déjà une demande (en attente ou approuvée) sur cette période.');
        }
 
        if (in_array($data['type'], DemandeConge::TYPES_DECOMPTES, true)) {
            $enAttente = (int) DemandeConge::where('employe_id', $user->id)
                ->where('statut', 'en_attente')
                ->whereIn('type', DemandeConge::TYPES_DECOMPTES)
                ->sum('nombre_jours');
            $disponible = (int) $user->getAttribute(DemandeConge::COLONNE_SOLDE) - $enAttente;
 
            if ($jours > $disponible) {
                $this->refuserSaisie("Solde insuffisant : {$jours} jour(s) demandé(s), {$disponible} disponible(s).");
            }
        }
 
        return $data + [
            'employe_id' => $user->id,
            'nombre_jours' => $jours,
            'statut' => 'en_attente',
        ];
    }
 
    private function refuserSaisie(string $message): void
    {
        Notification::make()->title($message)->danger()->send();
 
        $this->halt();
    }
}
