<?php

namespace App\Filament\Pages;

use App\Models\CommissionInventaire;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * Point d'entrée d'un membre de commission : il ne voit que SES commissions.
 * Accès par appartenance à une commission
 */
class MesComptages extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static string|UnitEnum|null $navigationGroup = 'Ressources Matérielles';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Mes comptages';

    protected static ?string $title = 'Mes comptages';

    protected static ?string $slug = 'mes-comptages';

    protected string $view = 'filament.pages.mes-comptages';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && self::requete((int) Auth::id())->exists();
    }

    /** Seuls les présidents voient leurs commissions ici. */
    private static function requete(int $userId)
    {
        return CommissionInventaire::whereHas('membres', fn ($q) => $q->where('user_id', $userId)->where('role', 'president'));
    }

    /** @return Collection<int, CommissionInventaire> */
    public function commissions(): Collection
    {
        return self::requete((int) Auth::id())
            ->with(['inventaire', 'membres.user'])
            ->latest('id')
            ->get();
    }
}
