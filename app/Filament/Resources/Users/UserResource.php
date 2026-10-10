<?php

namespace App\Filament\Resources\Users;

use App\Filament\DemoIconAlias;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserInfolist;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationIcon(): string | BackedEnum | null
    {
        return FilamentIcon::resolve(DemoIconAlias::RESOURCES_HR_EMPLOYEES_NAVIGATION) ?? Heroicon::OutlinedIdentification;
    }
    protected static string|UnitEnum|null $navigationGroup = 'HR';
    protected static ?string $navigationLabel = 'Employés'; 
    protected static ?string $modelLabel = 'Employé'; 

    protected static ?int $navigationSort = 1; 

    protected static ?string $recordTitleAttribute = 'Employees';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
