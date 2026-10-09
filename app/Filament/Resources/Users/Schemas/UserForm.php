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
                        ->columns(3)
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
                                ->columnSpan(2),
                            TextInput::make('nin')
                                ->label('Numéro d\'identification national (NIN)')
                                ->placeholder('10 988 0554 00345 0000')
                                ->regex('/^\d{18}$/')
                                ->validationMessages(['regex' => 'Le NIN doit contenir 18 chiffres.'])
                                ->maxLength(18)
                                ->required(),
                            TextInput::make('nom')
                                ->label('Nom')
                                ->placeholder('Entrez le nom')
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('prenom')
                                ->label('Prénom')
                                ->placeholder('Entrez le prénom')
                                ->required()
                                ->maxLength(255)
                                ->required(),
                            Select::make('sexe')
                                ->label('Sexe')
                                ->options(['masculin' => 'Masculin', 'feminin' => 'Féminin'])
                                ->required()
                                ->live(),
                            DatePicker::make('date_naissance')
                                ->label('Date de naissance')
                                ->maxDate(now())
                                ->required(),
                            Select::make('wilaya_naissance')
                                ->label('Wilaya de naissance')
                                ->options(self::wilayas())
                                ->searchable()
                                ->required(),
                            TextInput::make('commune_naissance')
                                ->label('Commune de naissance')
                                ->placeholder('Entrez la commune de naissance')
                                ->maxLength(255)
                                ->required(),
                            Select::make('situation_familiale')
                                ->label('Situation familiale')
                                ->options([
                                    'celibataire' => 'Célibataire',
                                    'marie' => 'Marié(e)',
                                    'divorce' => 'Divorcé(e)',
                                    'veuf' => 'Veuf / Veuve',
                                ])
                                ->default('celibataire')
                                ->required()
                                ->live(),
                            
                            TextInput::make('nombre_enfants')
                                ->label('Nombre d\'enfants')
                                ->placeholder('Entrez le nombre d\'enfants')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->live(onBlur: true)
                                ->visible(fn ($get) => $get('situation_familiale') !== 'celibataire'),
                            TextInput::make('nombre_enfants_a_charge')
                                ->label('Enfants à charge')
                                ->placeholder('Entrez le nombre d\'enfants à charge')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->maxValue(fn ($get) => (int) $get('nombre_enfants'))
                                ->helperText('Ne peut pas dépasser le nombre d\'enfants.')
                                ->visible(fn ($get) => $get('situation_familiale') !== 'celibataire'),
                            Select::make('situation_service_national')
                                ->label('Situation au service national')
                                ->options([
                                    'degage' => 'Dégagé',
                                    'incorpore' => 'Incorporé',
                                    'exempte' => 'Exempté',
                                    'sursitaire' => 'Sursitaire',
                                ])
                                ->default('degage')
                                ->visible(fn ($get) => $get('sexe') === 'masculin'),
                            TextInput::make('telephone')
                                ->label('Téléphone')
                                ->placeholder('06 12 34 56 78')
                                ->tel()
                                ->maxLength(20)
                                ->required(),
                            TextInput::make('email_personnel')
                                ->label('Email personnel')
                                ->placeholder('entrez@votre-email.com')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('adresse')
                                ->label('Adresse de résidence')
                                ->placeholder('Entrez votre adresse de résidence')
                                ->maxLength(255)
                                ->columnSpan(2)
                                ->required(),
                            Select::make('wilaya_residence')
                                ->label('Wilaya de résidence')
                                ->placeholder('Sélectionnez une wilaya')
                                ->options(self::wilayas())
                                ->searchable()
                                ->required(),
                            TextInput::make('commune_residence')
                                ->label('Commune de résidence')
                                ->placeholder('Entrez la commune de résidence')
                                ->maxLength(255)
                                ->required(),
                            Section::make('Pièces du dossier')
                                ->description('Formats acceptés : PDF, JPG, PNG (5 Mo maximum par fichier).')
                                ->columnSpanFull()
                                ->schema([
                                    self::documentsRepeater('documents_identite', 'piece_identite', 2),
                                    self::documentsRepeater('documents_naissance', 'extrait_naissance'),
                                    self::documentsRepeater('documents_residence', 'certificat_residence'),
                                    self::documentsRepeater('documents_familiale', 'fiche_familiale')
                                        ->visible(fn ($get) => $get('situation_familiale') !== 'celibataire'),
                                ]),
                        ]),
 
                    // ───────────── Emploi ─────────────
                    Tabs\Tab::make('Emploi')
                        ->icon('heroicon-o-briefcase')
                        ->columns(3)
                        ->schema([
                            DatePicker::make('date_embauche')
                                ->label('Date d\'entrée')
                                ->maxDate(now())
                                ->required(),    
                            Select::make('type_contrat')
                                ->label('Type de contrat')
                                ->options(['CDI' => 'CDI', 'CDD' => 'CDD', 'CTA' => 'CTA (contrat de travail aidé)'])
                                ->required(),
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
                                ->maxLength(255)
                                ->placeholder('ex: Ingénieur, Comptable, etc.')
                                ->required(),
                            TextInput::make('echelon')
                                ->label('Échelon')
                                ->maxLength(255)
                                ->placeholder('ex: 1, 2, 3, etc.')
                                ->required(),
                            TextInput::make('grade')
                                ->label('Grade')
                                ->maxLength(255)
                                ->placeholder('ex: A, B, C, etc.')
                                ->required(),
                            TextInput::make('classe')
                                ->label('Classe')
                                ->maxLength(255)
                                ->placeholder('ex: 1, 2, 3, etc.')
                                ->required(),
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
                                ->placeholder('ex: 8024360011')
                                ->regex('/^\d{10}$/')
                                ->validationMessages(['regex' => 'Le numéro CNAS doit contenir 10 chiffres.'])
                                ->maxLength(10)
                                ->required(),
                            TextInput::make('cle_cnas')
                                ->label('Clé CNAS')
                                ->placeholder('ex: 12')
                                ->regex('/^\d{2}$/')
                                ->validationMessages(['regex' => 'La clé doit contenir 2 chiffres.'])
                                ->maxLength(2)
                                ->required(),
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
                                ->placeholder('ex: 30003 01234 0001234567')
                                ->regex('/^\d{20}$/')
                                ->validationMessages(['regex' => 'Le RIP / RIB doit contenir 20 chiffres.'])
                                ->maxLength(20),
                            TextInput::make('code_banque_agence')
                                ->label('Code banque / agence')
                                ->placeholder('ex: 30003')
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
                                ])
                                ->required(),
                            TextInput::make('dernier_diplome')
                                ->label('Dernier diplôme obtenu')
                                ->placeholder('spécialité exacte')
                                ->maxLength(255)
                                ->required(),
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
                                ->placeholder('Entrez.votre@mail.com')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            TextInput::make('password')
                                ->label('Mot de passe')
                                ->placeholder('Entrez un mot de passe')
                                ->password()
                                ->revealable()
                                ->minLength(8)
                                ->required(fn (string $operation) => $operation === 'create')
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
