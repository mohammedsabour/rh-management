<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Schemas\Components\Tabs\Tab;

class ListUsers extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return UserResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('Tout')
                ->badge(static fn (): int => User::query()->count())
                ->lazyLoadedBadge(),
            'active' => Tab::make('Actif')
                ->badge(static fn (): int => User::query()->where('is_active', true)->count())
                ->badgeColor('success')
                ->lazyLoadedBadge()
                ->modifyQueryUsing(fn ($query) => $query->where('is_active', true)),
            'inactive' => Tab::make('Inactif')
                ->badge(static fn (): int => User::query()->where('is_active', false)->count())
                ->badgeColor('gray')
                ->lazyLoadedBadge()
                ->query(fn ($query) => $query->where('is_active', false)),
        ];
    }
}
