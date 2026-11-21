<?php

namespace App\Filament\Resources\Inventaires\Pages;

use App\Filament\Resources\Inventaires\InventaireResource;
use App\Models\User;
use App\Models\Inventaire;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateInventaire extends CreateRecord
{
    protected static string $resource = InventaireResource::class;

    protected static bool $canCreateAnother = false;

    private int $nombreEquipements = 0;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + [
            'statut' => 'comptage',
            'cree_par' => Auth::id(),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        $compositions = [
            'A' => [Arr::pull($data, 'a_president'), Arr::pull($data, 'a_membres')],
            'B' => [Arr::pull($data, 'b_president'), Arr::pull($data, 'b_membres')],
        ];

        $tous = collect($compositions)->flatMap(fn ($c) => [$c[0], ...($c[1] ?? [])])->filter();

        if ($tous->count() !== 6 || $tous->unique()->count() !== 6) {
            Notification::make()->danger()
                ->title('Composition invalide')
                ->body('Chaque commission compte un président et deux membres, et personne ne peut siéger dans les deux commissions.')
                ->send();

            $this->halt();
        }

        // Le créateur du récolement et les admins ne siègent jamais dans une commission (contrôle côté serveur)
        $interdit = $tous->contains(Auth::id())
            || User::whereIn('id', $tous)->whereHas('roles', fn ($q) => $q->where('name', 'admin'))->exists();

        if ($interdit) {
            Notification::make()->danger()
                ->title('Composition invalide')
                ->body('Le créateur du récolement et les administrateurs ne peuvent pas faire partie d\'une commission.')
                ->send();

            $this->halt();
        }

        return DB::transaction(function () use ($data, $compositions) {
            $inventaire = Inventaire::create($data);

            foreach ($compositions as $code => [$president, $membres]) {
                $commission = $inventaire->commissions()->create(['code' => $code]);
                $commission->membres()->create(['user_id' => $president, 'role' => 'president']);

                foreach ($membres as $membre) {
                    $commission->membres()->create(['user_id' => $membre, 'role' => 'membre']);
                }
            }

            $inventaire->load('commissions');
            $this->nombreEquipements = $inventaire->genererLignes();

            return $inventaire;
        });
    }

    protected function afterCreate(): void
    {
        $notification = Notification::make()->title($this->nombreEquipements . ' équipement(s) à contrôler par chaque commission');

        $this->nombreEquipements > 0
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