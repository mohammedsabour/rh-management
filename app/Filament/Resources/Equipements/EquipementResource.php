<?php

namespace App\Filament\Resources\Equipements;

use App\Filament\Resources\Equipements\Pages\CreateEquipement;
use App\Filament\Resources\Equipements\Pages\EditEquipement;
use App\Filament\Resources\Equipements\Pages\ListEquipements;
use App\Filament\Resources\Equipements\Pages\ViewEquipement;
use App\Filament\Resources\Equipements\Schemas\EquipementForm;
use App\Filament\Resources\Equipements\Schemas\EquipementInfolist;
use App\Filament\Resources\Equipements\Tables\EquipementsTable;
use App\Models\Equipement;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EquipementResource extends Resource
{
    protected static ?string $model = Equipement::class;

    protected static string|UnitEnum|null $navigationGroup = 'Ressources Matérielles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Equipement';

    public static function getGloballySearchableAttributes(): array
    {
        return ['code_barre', 'numero_inventaire', 'designation', 'numero_serie'];
    }

    public static function form(Schema $schema): Schema
    {
        return EquipementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EquipementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEquipements::route('/'),
            'create' => CreateEquipement::route('/create'),
            'view' => ViewEquipement::route('/{record}'),
            'edit' => EditEquipement::route('/{record}/edit'),
        ];
    }
}
