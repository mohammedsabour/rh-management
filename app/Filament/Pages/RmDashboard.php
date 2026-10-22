<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class RmDashboard extends BaseDashboard
{
    use HasPageShield;

    protected static string $routePath = 'Rm-Dashboard';

    protected static ?string $title = 'RM Dashboard';
    
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        return [
            
        ];
    }
}