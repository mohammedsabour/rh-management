<?php

namespace App\Filament\Resources\Equipements\Widgets;

use App\Filament\Resources\Equipements\Pages\ListEquipements;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class EquipementsStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListEquipements::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $totalEquipements = $query->count();
        $enService = (clone $query)->where('statut', 'affecte')->count();
        $enStock = (clone $query)->where('statut', 'en_stock')->count();
        $reforme = (clone $query)->where('statut', 'reforme')->count();

        return [
            Stat::make('Tout', $totalEquipements),
            Stat::make('En Service', $enService),
            Stat::make('En Stock', $enStock),
            Stat::make('Reformé', $reforme),
        ];
    }
}