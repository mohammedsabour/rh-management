<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class RmDashboard extends BaseDashboard
{
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