<?php

namespace App\Filament\Resources\Inventaires;

use App\Filament\Resources\Inventaires\Pages\CreateInventaire;
use App\Filament\Resources\Inventaires\Pages\EditInventaire;
use App\Filament\Resources\Inventaires\Pages\ListInventaires;
use App\Filament\Resources\Inventaires\Pages\ViewInventaire;
use App\Filament\Resources\Inventaires\Pages\ScannerInventaire;
use App\Filament\Resources\Inventaires\RelationManagers\LignesRelationManager;
use App\Filament\Resources\Inventaires\Schemas\InventaireForm;
use App\Filament\Resources\Inventaires\Schemas\InventaireInfolist;
use App\Filament\Resources\Inventaires\Tables\InventairesTable;
use App\Models\Inventaire;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InventaireResource extends Resource
{
    protected static ?string $model = Inventaire::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Ressources Matérielles';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'Inventaire';

    protected static ?string $modelLabel = 'récolement';
 
    protected static ?string $pluralModelLabel = 'récolements';
 
    protected static ?string $navigationLabel = 'Récolements';

    public static function form(Schema $schema): Schema
    {
        return InventaireForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InventaireInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventairesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LignesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventaires::route('/'),
            'create' => CreateInventaire::route('/create'),
            'view' => ViewInventaire::route('/{record}'),
            'edit' => EditInventaire::route('/{record}/edit'),
            'scanner' => ScannerInventaire::route('/{record}/scanner'),
        ];
    }
}
