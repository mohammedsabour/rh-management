<?php

namespace App\Filament\Resources\Inventaires\Schemas;

use App\Models\Emplacement;
use App\Models\Inventaire;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class InventaireForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Récolement')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('nom')
                        ->label('Intitulé')
                        ->placeholder('Ex. Récolement annuel 2026')
                        ->required()
                        ->columnSpanFull()
                        ->maxLength(255),

                    DatePicker::make('date_debut')
                        ->label('Date de début')
                        ->default(today())
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    Select::make('emplacement_id')
                        ->label('Périmètre')
                        ->placeholder('Tous les emplacements')
                        ->helperText('Les deux commissions comptent exactement le même périmètre. Non modifiable après la création.')
                        ->options(fn () => Emplacement::with('parent.parent.parent')->get()
                            ->mapWithKeys(fn (Emplacement $e) => [$e->id => $e->chemin_complet])
                            ->sort())
                        ->searchable()
                        ->disabled(fn (?Inventaire $record) => $record !== null),

                    TextInput::make('decision_numero')
                        ->label('N° de la décision de nomination des commissions')
                        ->placeholder('Ex. 12/2026')
                        ->maxLength(50)
                        ->unique(table: Inventaire::class, column: 'decision_numero', ignoreRecord: true)
                        ->required(),

                    DatePicker::make('decision_date')
                        ->label('Date de la décision')
                        ->native(false)
                        ->placeholder('DD/MM/YY')
                        ->maxDate(today())
                        ->displayFormat('d/m/Y')
                        ->required(),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            self::commission('A', 'a'),
            self::commission('B', 'b'),
        ]);
    }

    private static function commission(string $lettre, string $cle): Section
    {
        // Ni le créateur du récolement, ni un admin ne peuvent siéger dans une commission
        $utilisateurs = fn () => User::where('is_active', true)
            ->where('id', '!=', Auth::id())
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->orderBy('nom')->get()
            ->mapWithKeys(fn (User $u) => [$u->id => trim($u->prenom . ' ' . $u->nom)])
            ->all();

        return Section::make("Commission {$lettre}")
            ->description('Un président et deux autres membres. Une même personne ne peut siéger que dans une seule commission.')
            ->columns(2)
            ->visibleOn('create')
            ->schema([
                Select::make("{$cle}_president")
                    ->label('Président')
                    ->options($utilisateurs)
                    ->searchable()
                    ->required(),

                Select::make("{$cle}_membres")
                    ->label('Autres membres (2)')
                    ->options($utilisateurs)
                    ->multiple()
                    ->minItems(2)
                    ->maxItems(2)
                    ->searchable()
                    ->required(),
            ]);
    }
}