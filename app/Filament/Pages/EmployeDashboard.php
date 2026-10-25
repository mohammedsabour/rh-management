<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use App\Filament\DemoIconAlias;
use App\Filament\Widgets\EmployeWorkforceInsightsStats;

class EmployeDashboard extends BaseDashboard
{
    use HasPageShield;

    protected static string $routePath = 'Employe-Dashboard';

    protected static ?string $title = 'Dashboard';
    
    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        return [
            EmployeWorkforceInsightsStats::class,
        ];
    }
}