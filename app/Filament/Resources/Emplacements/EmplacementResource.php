<?php

namespace App\Filament\Resources\Emplacements;

use App\Filament\Resources\Emplacements\Pages\CreateEmplacement;
use App\Filament\Resources\Emplacements\Pages\EditEmplacement;
use App\Filament\Resources\Emplacements\Pages\ListEmplacements;
use App\Filament\Resources\Emplacements\Schemas\EmplacementForm;
use App\Filament\Resources\Emplacements\Tables\EmplacementsTable;
use App\Models\Emplacement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmplacementResource extends Resource
{
    protected static ?string $model = Emplacement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Ressources Matérielles';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'emplacement';

    protected static ?string $pluralModelLabel = 'emplacements';

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return EmplacementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmplacementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmplacements::route('/'),
            'create' => CreateEmplacement::route('/create'),
            'edit' => EditEmplacement::route('/{record}/edit'),
        ];
    }
}