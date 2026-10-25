<?php

namespace App\Filament\Pages;

use App\Filament\DemoIconAlias;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\WorkforceInsightsStats;
use App\Filament\Widgets\EmployeesByDepartmentChart;
use App\Filament\Widgets\AbsencesPerEmployeeChart;
use App\Filament\Widgets\EmployeesByContractChart;
use App\Filament\Widgets\AbsencesByTypeChart;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use BackedEnum;

class RhDashboard extends BaseDashboard
{
    use HasPageShield;
    
    protected static string $routePath = 'Dashboard';

    protected static ?string $title = 'HR Dashboard';

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return FilamentIcon::resolve(DemoIconAlias::PAGES_HR_DASHBOARD_NAVIGATION) ?? Heroicon::OutlinedBriefcase;
    }
    
    public function getWidgets(): array
    {
        return [
            // Add your widgets here
            WorkforceInsightsStats::class,
            EmployeesByDepartmentChart::class,
            AbsencesPerEmployeeChart::class,
            EmployeesByContractChart::class,
            AbsencesByTypeChart::class,
        ];
    }
}
