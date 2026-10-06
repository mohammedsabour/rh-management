<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class  InventaireDashboard extends BaseDashboard
{
    protected static string $routePath = 'Inventaire';

    protected static ?string $title = 'Inventaire Dashboard';
    
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?int $navigationSort = 2;

    public function getWidgets(): array
    {
        return [
            
        ];
    }
}