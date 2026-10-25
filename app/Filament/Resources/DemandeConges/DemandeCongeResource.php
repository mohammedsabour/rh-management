<?php

namespace App\Filament\Resources\DemandeConges;

use App\Filament\Resources\DemandeConges\Pages\CreateDemandeConge;
use App\Filament\Resources\DemandeConges\Pages\ListDemandeConges;
use App\Filament\Resources\DemandeConges\Pages\ViewDemandeConge;
use App\Filament\Resources\DemandeConges\Tables\DemandeCongesTable;
use App\Models\DemandeConge;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DemandeCongeResource extends Resource
{
    protected static ?string $model = DemandeConge::class;
 
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
 
    protected static string|UnitEnum|null $navigationGroup = 'Ressources Humaines';

    protected static ?string $modelLabel = 'demande de congé';
 
    protected static ?string $pluralModelLabel = 'demandes de congé';
 
    protected static ?string $navigationLabel = 'Demandes de congé';
 
    protected static ?int $navigationSort = 4;
 
    /** Vrai pour RH / admin : droit de voir les demandes de TOUS les employés. */
    public static function peutVoirTout(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
 
        return (bool) $user?->can('ViewAny:DemandeConge');
    }
 
    // Filtre de sécurité : sans le droit « voir tout », on ne voit que ses propres demandes
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
 
        return self::peutVoirTout() ? $query : $query->where('employe_id', Auth::id());
    }
 
    // Pastille « à traiter » : seulement pour ceux qui peuvent les traiter
    public static function getNavigationBadge(): ?string
    {
        if (! self::peutVoirTout()) {
            return null;
        }
 
        $n = DemandeConge::enAttente()->count();
 
        return $n > 0 ? (string) $n : null;
    }
 
    public static function form(Schema $schema): Schema
    {
        /** @var User $user */
        $user = Auth::user();
        $solde = (int) $user->getAttribute(DemandeConge::COLONNE_SOLDE);
 
        return $schema->components([
            Select::make('type')
                ->label('Type de congé')
                ->options(DemandeConge::TYPES)
                ->default('annuel')
                ->required()
                ->native(false)
                ->helperText("Votre solde de congé annuel : {$solde} jour(s)."),
            DatePicker::make('date_debut')
                ->label('Du')
                ->required()
                ->minDate(today())
                ->native(false)
                ->displayFormat('d/m/Y')
                ->live(),
            DatePicker::make('date_fin')
                ->label('Au')
                ->required()
                ->afterOrEqual('date_debut')
                ->native(false)
                ->displayFormat('d/m/Y'),
            Textarea::make('commentaire')
                ->label('Commentaire (facultatif)')
                ->maxLength(1000)
                ->columnSpanFull(),
        ])->columns(2);
    }
 
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
 
    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('employe')
                ->label('Employé')
                ->state(fn (DemandeConge $r) => trim($r->employe->prenom.' '.$r->employe->nom)),
            TextEntry::make('employe.departement.nom')->label('Département')->placeholder('—'),
            TextEntry::make('type')
                ->label('Type')
                ->formatStateUsing(fn (string $state) => DemandeConge::TYPES[$state] ?? $state),
            TextEntry::make('date_debut')->label('Du')->date('d/m/Y'),
            TextEntry::make('date_fin')->label('Au')->date('d/m/Y'),
            TextEntry::make('nombre_jours')->label('Jours ouvrés'),
            TextEntry::make('employe.joures_conges_restant')->label('Solde actuel de l\'employé'),
            TextEntry::make('commentaire')->label('Commentaire de l\'employé')->placeholder('—'),
            TextEntry::make('statut')
                ->label('Statut')
                ->badge()
                ->formatStateUsing(fn (string $state) => DemandeConge::STATUTS[$state] ?? $state)
                ->color(fn (string $state) => match ($state) {
                    'approuve' => 'success',
                    'refuse' => 'danger',
                    'annule' => 'gray',
                    default => 'warning',
                }),
            TextEntry::make('traitePar')
                ->label('Traité par')
                ->state(fn (DemandeConge $r) => $r->traitePar
                    ? trim($r->traitePar->prenom.' '.$r->traitePar->nom)
                    : null)
                ->placeholder('—'),
            TextEntry::make('date_traitement')->label('Date de traitement')->dateTime('d/m/Y H:i')->placeholder('—'),
            TextEntry::make('motif_refus')->label('Motif du refus')->placeholder('—'),
            TextEntry::make('created_at')->label('Demande envoyée le')->dateTime('d/m/Y H:i'),
        ]);
    }
 
    public static function table(Table $table): Table
    {
        return DemandeCongesTable::configure($table);
    }
 
    public static function getPages(): array
    {
        return [
            'index' => ListDemandeConges::route('/'),
            'create' => CreateDemandeConge::route('/create'),
            'view' => ViewDemandeConge::route('/{record}'),
        ];
    }
}