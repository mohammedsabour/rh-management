<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\User;
use App\Filament\Resources\Users\Pages\ListUsers;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class EmployeeStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListUsers::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $totalEmployees = $query->count();
        $activeEmployees = (clone $query)->where('is_active', true)->count();
        $avgTenure = (clone $query)->whereNotNull('date_embauche')
            ->pluck('date_embauche')
            ->avg(fn (mixed $date): float => Carbon::parse($date)->diffInDays(now()) / 365.25);
        return [
            Stat::make('Total Employees', $totalEmployees),
            Stat::make('Active Employees', $activeEmployees)
                ->color('success'),
            Stat::make('Avg Tenure', number_format((float) $avgTenure, 1) . ' years')
                ->color('warning'),
        ];
    }
}