<?php

namespace App\Filament\Resources\Equipements\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Emplacement;
use App\Models\CategorieEquipement;
use App\Models\Equipement;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;

class EquipementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::schema());
    }

    public static function schema(): array
    {
        return [
            Tabs::make('Equipement')
                ->tabs([
                    Tabs\Tab::make('Identification')
                        ->icon('heroicon-o-identification')
                        ->columns(2)
                        ->schema([
                            TextInput::make('code_barre')
                                ->label('Code-barres')
                                ->helperText('Généré automatiquement à la création')
                                ->disabled()
                                ->dehydrated(false)
                                ->visibleOn('edit'),
                            FileUpload::make('photo')
                                ->label('Photo')
                                ->image()
                                ->disk('local')
                                ->directory('equipements')
                                ->visibility('private')
                                ->maxSize(4096),
                            TextInput::make('numero_inventaire')
                                ->label("N° d'inventaire")
                                ->helperText("Non modifiable après l'ajout")
                                ->disabled(fn (string $operation) => $operation === 'edit')
                                ->dehydrated(fn (string $operation) => $operation === 'create')
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true),
                            TextInput::make('designation')
                                ->label('Désignation')
                                ->placeholder('Ex. PC portable Dell Latitude 5440')
                                ->required()
                                ->maxLength(255)
                                ->required(),
                            Select::make('categorie_id')
                                ->label('Catégorie')
                                ->disabled(fn (string $operation) => $operation === 'edit')
                                ->dehydrated(fn (string $operation) => $operation === 'create')
                                ->relationship('categorie', 'nom')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('nom')
                                        ->label('Nom du Catégorie')
                                        ->maxLength(255)
                                        ->unique(CategorieEquipement::class, 'nom')
                                        ->required(),
                                    TextInput::make('code')
                                        ->label('Code du Catégorie')
                                        ->placeholder('ex. INF, MOB, ect.')
                                        ->required()
                                        ->maxLength(5)
                                        ->alphaDash()
                                        ->unique(CategorieEquipement::class, 'code')
                                        ->dehydrateStateUsing(fn (?string $state) => strtoupper(trim((string) $state))),
                                    TextInput::make('numero_compte')
                                        ->label('Numéro de compte (SCF)')
                                        ->numeric()
                                        ->minLength(3)
                                        ->required(),
                                ])
                                ->createOptionModalHeading('Nouvelle catégorie')
                                ->helperText(fn (string $operation) => $operation === 'edit' ? "Non modifiable après l'ajout" : null),
                            TextInput::make('marque')
                                ->label('Marque')
                                ->placeholder('Ex. Dell, HP, Homely, etc.')
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('modele')
                                ->label('Modèle')
                                ->placeholder('Ex. Latitude 5440, PIXMA TS3751i, etc.')
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('numero_serie')
                                ->label('N° de série')
                                ->placeholder('Ex. 053X3HGR...')
                                ->maxLength(255)
                                ->required(),
                        ]),
                    Tabs\Tab::make('Situation')
                        ->icon('heroicon-o-map-pin')
                        ->columns(2)
                        ->schema([
                            Select::make('etat')
                                ->label('État physique')
                                ->options(Equipement::ETATS)
                                ->default('neuf')
                                ->disabled()
                                ->dehydrated(false)
                                ->native(false),
                            
                            Select::make('statut')
                                ->label('Statut')
                                ->options(Equipement::STATUTS)
                                ->default('en_stock')
                                ->disabled()
                                ->dehydrated(false)
                                ->native(false),

                            Select::make('emplacement_id')
                                ->label('Emplacement')
                                ->disabled(fn (string $operation) => $operation === 'edit')
                                ->dehydrated(fn (string $operation) => $operation === 'create')
                                ->relationship('emplacement', 'nom')
                                ->getOptionLabelFromRecordUsing(fn (Emplacement $record) => $record->chemin_complet)
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('nom')
                                        ->label('Nom')
                                        ->placeholder('Ex. Bureau 12')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('code')
                                        ->label('Code')
                                        ->helperText('Facultatif (ex. A-1-12)')
                                        ->maxLength(20)
                                        ->unique(ignoreRecord: true),
                                    Select::make('type')
                                        ->label('Type')
                                        ->options(Emplacement::TYPES)
                                        ->default('bureau')
                                        ->required()
                                        ->native(false),
                                    Select::make('departement_id')
                                        ->label('Département')
                                        ->placeholder('Aucun')
                                        ->relationship('departement', 'nom')
                                        ->searchable()
                                        ->preload()
                                        ->columnSpanFull(),
                                ])
                                ->createOptionModalHeading('Nouvel emplacement')
                                ->helperText(fn (string $operation) => $operation === 'create' ? "Choisissez l'emplacement du stock" : null)
                                ->columnSpanFull(),
                        ]),

                    Tabs\Tab::make('Acquisition')
                        ->icon('heroicon-o-shopping-cart')
                        ->columns(2)
                        ->schema([
                            DatePicker::make('date_acquisition')
                                ->label("Date d'acquisition")
                                ->required(),
                            TextInput::make('prix_acquisition')
                                ->label("Prix d'acquisition")
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->suffix('DA')
                                ->required(),
                            TextInput::make('reference_facture')
                                ->label("Référence de la facture")
                                ->placeholder('Ex. FA-2026-001')
                                ->required(),
                            DatePicker::make('date_fin_garantie')
                                ->label("Date de fin de garantie"),
                            Textarea::make('notes')
                                ->columnSpanFull(),
                        ]),
            ])
            ->persistTabInQueryString()
            ->columnSpanFull(),
        ];
    }
}
