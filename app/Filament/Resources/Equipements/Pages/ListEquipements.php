<?php

namespace App\Filament\Resources\Equipements\Pages;

use App\Filament\Resources\Equipements\EquipementResource;
use App\Models\Emplacement;
use App\Models\Equipement;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\Tabs\Tab;

class ListEquipements extends ListRecords
{
    use ExposesTableToWidgets;
    
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
            
            Action::make('export_par_emplacement')
                ->label('Exporter')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->modalHeading('Exporter les équipements par emplacement')
                ->modalSubmitActionLabel('Générer le PDF')
                ->schema([
                    Select::make('emplacement')
                        ->label('Emplacement')
                        ->placeholder('Tous les emplacements')
                        ->options(fn () => Emplacement::orderBy('nom')->get()
                            ->mapWithKeys(fn (Emplacement $e) => [$e->id => $e->chemin_complet])
                            ->sort()
                            ->all())
                        ->searchable(),
                ])
                ->action(function (array $data, $livewire) {
                    $url = route('inventaire.par-emplacement', array_filter(['emplacement' => $data['emplacement'] ?? null]));
 
                    // Ouvre le PDF dans un nouvel onglet, comme « Inventaire comptable »
                    $livewire->js('window.open(' . json_encode($url) . ", '_blank')");
                }),
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return EquipementResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('Tout')
                ->badge(static fn (): int => Equipement::query()->count())
                ->lazyLoadedBadge(),
            'En_service' => Tab::make('En service')
                ->badge(static fn (): int => Equipement::query()->where('statut', 'affecte')->count())
                ->badgeColor('success')
                ->lazyLoadedBadge()
                ->modifyQueryUsing(fn ($query) => $query->where('statut', 'affecte')),
            'En_Stock' => Tab::make('En Stock')
                ->badge(static fn (): int => Equipement::query()->where('statut', 'en_stock')->count())
                ->badgeColor('gray')
                ->lazyLoadedBadge()
                ->query(fn ($query) => $query->where('statut', 'en_stock')),
        ];
    }
}
