<?php

namespace App\Filament\Resources\Departements;

use App\Filament\Resources\Departements\Pages\CreateDepartement;
use App\Filament\Resources\Departements\Pages\EditDepartement;
use App\Filament\Resources\Departements\Pages\ListDepartements;
use App\Filament\Resources\Departements\Schemas\DepartementForm;
use App\Filament\Resources\Departements\Tables\DepartementsTable;
use App\Models\Departement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DepartementResource extends Resource
{
    protected static ?string $model = Departement::class;

    protected static string|UnitEnum|null $navigationGroup = 'HR';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;
    protected static ?int $navigationSort = 3; 

    protected static ?string $recordTitleAttribute = 'Departements';

    public static function form(Schema $schema): Schema
    {
        return DepartementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartementsTable::configure($table);
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
            'index' => ListDepartements::route('/'),
            'create' => CreateDepartement::route('/create'),
            'edit' => EditDepartement::route('/{record}/edit'),
        ];
    }
}
