<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\ViewAction;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    private const CHAMPS_MASQUES = [
        'nin',
        'numero_assurance_sociale',
        'cle_cnas',
        'numero_compte',
        'code_banque_agence',
    ];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (self::CHAMPS_MASQUES as $champ) {
            $data[$champ] = $this->getRecord()->getAttribute($champ);
        }
 
        return $data;
    }
}
