<?php

namespace App\Filament\Resources\Equipements\Pages;

use App\Filament\Resources\Equipements\EquipementResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipement extends CreateRecord
{
    protected static string $resource = EquipementResource::class;

    protected static bool $canCreateAnother = false;

    private const MESSAGE = "Le N° d'inventaire et la catégorie ne pourront plus être modifiés après la création. Voulez-vous continuer ?";

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->submit(null)
            ->action(function () {
                $this->form->validate();

                $this->replaceMountedAction('confirmerCreation');
            });
    }

    public function confirmerCreationAction(): Action
    {
        return Action::make('confirmerCreation')
            ->requiresConfirmation()
            ->modalHeading('Attention')
            ->modalDescription(self::MESSAGE)
            ->modalSubmitActionLabel('Oui, créer')
            ->modalCancelActionLabel('Non, revenir')
            ->action(fn () => $this->create());
    }
}
