<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;

class UserForm
{
    private const WILAYAS = [
        'Adrar', 'Chlef', 'Laghouat', 'Oum El Bouaghi', 'Batna', 'Béjaïa', 'Biskra', 'Béchar',
        'Blida', 'Bouira', 'Tamanrasset', 'Tébessa', 'Tlemcen', 'Tiaret', 'Tizi Ouzou', 'Alger',
        'Djelfa', 'Jijel', 'Sétif', 'Saïda', 'Skikda', 'Sidi Bel Abbès', 'Annaba', 'Guelma',
        'Constantine', 'Médéa', 'Mostaganem', "M'Sila", 'Mascara', 'Ouargla', 'Oran', 'El Bayadh',
        'Illizi', 'Bordj Bou Arréridj', 'Boumerdès', 'El Tarf', 'Tindouf', 'Tissemsilt', 'El Oued',
        'Khenchela', 'Souk Ahras', 'Tipaza', 'Mila', 'Aïn Defla', 'Naâma', 'Aïn Témouchent',
        'Ghardaïa', 'Relizane', 'Timimoun', 'Bordj Badji Mokhtar', 'Ouled Djellal', 'Béni Abbès',
        'In Salah', 'In Guezzam', 'Touggourt', 'Djanet', "El M'Ghair", 'El Meniaa',
    ];
    private static function wilayas(): array
    {
        return array_combine(self::WILAYAS, self::WILAYAS);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::schema());
    }

    private static function documentsRepeater(string $key, string $type, int $max = 1): Repeater
    {
        return Repeater::make($key)
            ->label(Document::TYPES[$type])
            ->relationship('documents', modifyQueryUsing: fn (Builder $query) => $query->where('type', $type))
            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => $data + ['type' => $type])
            ->schema([
                FileUpload::make('file_path')
                    ->label('Fichier')
                    ->disk(Document::DISK)
                    ->directory('employes/documents')
                    ->visibility('private')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(5120)
                    ->storeFileNamesIn('file_name')
                    ->openable()
                    ->downloadable()
                    ->required(),
            ])
            ->itemLabel(fn (array $state): string => $state['file_name'] ?? 'Fichier')
            ->addActionLabel('Ajouter un fichier')
            ->defaultItems(0)
            ->maxItems($max)
            ->reorderable(false)
            ->columnSpanFull();
    }
 
    public static function schema(): array
    {
        return [
            Tabs::make('Employé')
                ->tabs([
                    // ───────────── État civil ─────────────
                    Tabs\Tab::make('État civil')
                        ->icon('heroicon-o-user')
                        ->columns(2)
                        ->schema([
                            FileUpload::make('photo')
                                ->label('Photo')
                                ->image()
                                ->avatar()
                                ->imageEditor()
                                ->disk('public')
                                ->visibility('public')
                                ->directory('photos')
                                ->maxSize(2048)
                                ->columnSpanFull(),
                            TextInput::make('nom')
                                ->label('Nom')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('prenom')
                                ->label('Prénom')
                                ->required()
                                ->maxLength(255),
                            DatePicker::make('date_naissance')
                                ->label('Date de naissance')
                                ->maxDate(now()),
                            Select::make('sexe')
                                ->label('Sexe')
                                ->options(['masculin' => 'Masculin', 'feminin' => 'Féminin'])
                                ->live(),
                            Select::make('wilaya_naissance')
                                ->label('Wilaya de naissance')
                                ->options(self::wilayas())
                                ->searchable(),
                            TextInput::make('commune_naissance')
                                ->label('Commune de naissance')
                                ->maxLength(255),
                            Select::make('situation_familiale')
                                ->label('Situation familiale')
                                ->options([
                                    'celibataire' => 'Célibataire',
                                    'marie' => 'Marié(e)',
                                    'divorce' => 'Divorcé(e)',
                                    'veuf' => 'Veuf / Veuve',
                                ])
                                ->live(),
                            TextInput::make('nin')
                                ->label('Numéro d\'identification national (NIN)')
                                ->regex('/^\d{18}$/')
                                ->validationMessages(['regex' => 'Le NIN doit contenir 18 chiffres.'])
                                ->maxLength(18),
                            TextInput::make('nombre_enfants')
                                ->label('Nombre d\'enfants')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->live(onBlur: true),
                            TextInput::make('nombre_enfants_a_charge')
                                ->label('Enfants à charge')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->maxValue(fn ($get) => (int) $get('nombre_enfants'))
                                ->helperText('Ne peut pas dépasser le nombre d\'enfants.'),
                            Select::make('situation_service_national')
                                ->label('Situation au service national')
                                ->options([
                                    'degage' => 'Dégagé',
                                    'incorpore' => 'Incorporé',
                                    'exempte' => 'Exempté',
                                    'sursitaire' => 'Sursitaire',
                                ])
                                ->visible(fn ($get) => $get('sexe') === 'masculin'),
                            TextInput::make('telephone')
                                ->label('Téléphone')
                                ->tel()
                                ->maxLength(20),
                            TextInput::make('email_personnel')
                                ->label('Email personnel')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('adresse')
                                ->label('Adresse de résidence')
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Select::make('wilaya_residence')
                                ->label('Wilaya de résidence')
                                ->options(self::wilayas())
                                ->searchable(),
                            TextInput::make('commune_residence')
                                ->label('Commune de résidence')
                                ->maxLength(255),
                            Section::make('Pièces du dossier')
                                ->description('Formats acceptés : PDF, JPG, PNG (5 Mo maximum par fichier).')
                                ->columnSpanFull()
                                ->schema([
                                    self::documentsRepeater('documents_identite', 'piece_identite', 2)
                                        ->helperText('Recto et verso si nécessaire.'),
                                    self::documentsRepeater('documents_naissance', 'extrait_naissance'),
                                    self::documentsRepeater('documents_residence', 'certificat_residence'),
                                    self::documentsRepeater('documents_familiale', 'fiche_familiale')
                                        ->visible(fn ($get) => $get('situation_familiale') === 'marie'),
                                ]),
                        ]),
 
                    // ───────────── Emploi ─────────────
                    Tabs\Tab::make('Emploi')
                        ->icon('heroicon-o-briefcase')
                        ->columns(2)
                        ->schema([
                            DatePicker::make('date_embauche')
                                ->label('Date d\'entrée'),
                            Select::make('type_contrat')
                                ->label('Type de contrat')
                                ->options(['CDI' => 'CDI', 'CDD' => 'CDD', 'CTA' => 'CTA (contrat de travail aidé)']),
                            Select::make('departement_id')
                                ->label('Département')
                                ->relationship('departement', 'nom')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('nom')
                                        ->label('Nom du département')
                                        ->required()
                                        ->unique('departements', 'nom'),
                                ]),
                            TextInput::make('poste')
                                ->label('Poste occupé')
                                ->maxLength(255),
                            TextInput::make('echelon')
                                ->label('Échelon')
                                ->maxLength(255),
                            TextInput::make('grade')
                                ->label('Grade')
                                ->maxLength(255),
                            TextInput::make('classe')
                                ->label('Classe')
                                ->maxLength(255),
                            // Affichage seul : le solde n'est pas dans $fillable, il ne change que par la logique des congés
                            TextInput::make('jours_conges_restant')
                                ->label('Jours de congés restants')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(false)
                                ->visibleOn('edit'),
                            Section::make('Pièces du dossier')
                                ->columnSpanFull()
                                ->schema([
                                    self::documentsRepeater('documents_contrat', 'contrat_travail'),
                                ]),
                        ]),
 
                    // ───────────── Sécurité sociale & paiement ─────────────
                    Tabs\Tab::make('Sécurité sociale & paiement')
                        ->icon('heroicon-o-banknotes')
                        ->columns(2)
                        ->schema([
                            TextInput::make('numero_assurance_sociale')
                                ->label('N° de sécurité sociale (CNAS)')
                                ->regex('/^\d{10}$/')
                                ->validationMessages(['regex' => 'Le numéro CNAS doit contenir 10 chiffres.'])
                                ->maxLength(10),
                            TextInput::make('cle_cnas')
                                ->label('Clé CNAS')
                                ->regex('/^\d{2}$/')
                                ->validationMessages(['regex' => 'La clé doit contenir 2 chiffres.'])
                                ->maxLength(2),
                            Toggle::make('conjoint_travaille')
                                ->label('Le conjoint travaille')
                                ->visible(fn ($get) => $get('situation_familiale') === 'marie'),
                            Select::make('mode_paiement')
                                ->label('Mode de paiement')
                                ->options([
                                    'virement_ccp' => 'Virement CCP',
                                    'virement_bancaire' => 'Virement bancaire',
                                ]),
                            TextInput::make('numero_compte')
                                ->label('N° de compte (RIP / RIB)')
                                ->regex('/^\d{20}$/')
                                ->validationMessages(['regex' => 'Le RIP / RIB doit contenir 20 chiffres.'])
                                ->maxLength(20),
                            TextInput::make('code_banque_agence')
                                ->label('Code banque / agence')
                                ->maxLength(255),
                        ]),
 
                    // ───────────── Compétences & formations ─────────────
                    Tabs\Tab::make('Compétences & formations')
                        ->icon('heroicon-o-academic-cap')
                        ->columns(2)
                        ->schema([
                            Select::make('niveau_etude')
                                ->label('Niveau d\'étude')
                                ->options([
                                    'Bac' => 'Bac',
                                    'Bac+2' => 'Bac+2',
                                    'Bac+3' => 'Bac+3 (Licence)',
                                    'Bac+4' => 'Bac+4',
                                    'Bac+5' => 'Bac+5 (Master / Ingénieur)',
                                    'Doctorat' => 'Doctorat',
                                ]),
                            TextInput::make('dernier_diplome')
                                ->label('Dernier diplôme obtenu (spécialité exacte)')
                                ->maxLength(255),
                            Section::make('Pièces du dossier')
                                ->columnSpanFull()
                                ->schema([
                                    self::documentsRepeater('documents_diplome', 'diplome', 5),
                                ]),
                        ]),
 
                    // ───────────── Compte ─────────────
                    Tabs\Tab::make('Compte')
                        ->icon('heroicon-o-key')
                        ->columns(2)
                        ->schema([
                            TextInput::make('email')
                                ->label('Email de connexion')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            TextInput::make('password')
                                ->label('Mot de passe')
                                ->password()
                                ->revealable()
                                ->minLength(8)
                                ->required(fn (string $operation) => $operation === 'create')
                                // Le cast 'hashed' du modèle s'occupe du hachage : pas de Hash::make ici
                                ->dehydrated(fn ($state) => filled($state))
                                ->helperText('En modification, laissez vide pour conserver le mot de passe actuel.'),
                            Select::make('role')
                                ->label('Rôle')
                                ->options([
                                    'admin' => 'Administrateur',
                                    'rh' => 'RH',
                                    'employe' => 'Employé',
                                ])
                                ->default('employe')
                                ->required(),
                            Select::make('status')
                                ->label('Statut')
                                ->options(['actif' => 'Actif', 'inactif' => 'Inactif'])
                                ->default('actif')
                                ->required(),
                        ]),
                ])
                ->persistTabInQueryString()
                ->columnSpanFull(),
        ];
    }
}
