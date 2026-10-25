<?php

namespace App\Filament\Resources\DemandeConges\Pages;

use App\Filament\Resources\DemandeConges\DemandeCongeResource;
use App\Models\DemandeConge;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListDemandeConges extends ListRecords
{
    protected static string $resource = DemandeCongeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nouvelle demande'),
        ];
    }
    public function getTabs(): array
    {
        $mes = Tab::make('Mes demandes')
            ->icon('heroicon-o-user')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('employe_id', Auth::id()));

        if (! DemandeCongeResource::peutVoirTout()) {
            return ['mes' => $mes];
        }

        return [
            'a_traiter' => Tab::make('À traiter')
                ->icon('heroicon-o-clock')
                ->badge(DemandeConge::enAttente()->count() ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'en_attente')),
            'toutes' => Tab::make('Toutes les demandes'),
            'mes' => $mes,
        ];
    }
    public function getDefaultActiveTab(): string|int|null
    {
        return DemandeCongeResource::peutVoirTout() ? 'a_traiter' : 'mes';
    }
}