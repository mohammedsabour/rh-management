<?php
 
namespace App\Filament\Resources\Users\Schemas;
 
use App\Models\Document;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
 
class UserInfolist
{
    private const SEXES = ['masculin' => 'Masculin', 'feminin' => 'Féminin'];
 
    private const SITUATIONS_FAMILIALES = [
        'celibataire' => 'Célibataire',
        'marie' => 'Marié(e)',
        'divorce' => 'Divorcé(e)',
        'veuf' => 'Veuf / Veuve',
    ];
 
    private const SERVICE_NATIONAL = [
        'degage' => 'Dégagé',
        'incorpore' => 'Incorporé',
        'exempte' => 'Exempté',
        'sursitaire' => 'Sursitaire',
    ];
 
    private const MODES_PAIEMENT = [
        'virement_ccp' => 'Virement CCP',
        'virement_bancaire' => 'Virement bancaire',
    ];
 
    private const ROLES = ['admin' => 'Administrateur', 'rh' => 'RH', 'employe' => 'Employé'];
 
    private const STATUTS = ['actif' => 'Actif', 'inactif' => 'Inactif'];
 
    // Appelée par UserResource::infolist()
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // ───────────── En-tête ─────────────
            Section::make()
                ->columns(['default' => 1, 'md' => 4])
                ->schema([
                    ImageEntry::make('photo')
                        ->hiddenLabel()
                        ->disk('public')
                        ->visibility('public')
                        ->circular()
                        ->size(110),
                    Group::make([
                        TextEntry::make('nom_complet')
                            ->hiddenLabel()
                            ->weight('bold')
                            ->state(fn ($record) => trim("{$record->prenom} {$record->nom}"))
                            ->columnSpanFull(),
                        self::text('poste', 'Poste'),
                        self::text('departement.nom', 'Département'),
                        self::badge('status', 'Statut', self::STATUTS),
                    ])
                        ->columns(2)
                        ->columnSpan(['md' => 3]),
                ]),
 
            Tabs::make('Employé')
                ->tabs([
                    // ───────────── État civil ─────────────
                    Tabs\Tab::make('État civil')
                        ->icon('heroicon-o-user')
                        ->columns(2)
                        ->schema([
                            self::text('nom', 'Nom'),
                            self::text('prenom', 'Prénom'),
                            self::text('date_naissance', 'Date de naissance')->date('d/m/Y'),
                            self::text('sexe', 'Sexe')
                                ->formatStateUsing(fn (?string $state) => self::SEXES[$state] ?? $state),
                            self::text('wilaya_naissance', 'Wilaya de naissance'),
                            self::text('commune_naissance', 'Commune de naissance'),
                            self::text('situation_familiale', 'Situation familiale')
                                ->formatStateUsing(fn (?string $state) => self::SITUATIONS_FAMILIALES[$state] ?? $state),
                            self::masked('nin', 'NIN'),
                            self::text('nombre_enfants', 'Nombre d\'enfants'),
                            self::text('nombre_enfants_a_charge', 'Enfants à charge'),
                            self::text('situation_service_national', 'Service national')
                                ->formatStateUsing(fn (?string $state) => self::SERVICE_NATIONAL[$state] ?? $state)
                                ->visible(fn ($record) => $record->sexe === 'masculin'),
                            self::text('telephone', 'Téléphone'),
                            self::text('email_personnel', 'Email personnel'),
                            self::text('adresse', 'Adresse de résidence')->columnSpanFull(),
                            self::text('wilaya_residence', 'Wilaya de résidence'),
                            self::text('commune_residence', 'Commune de résidence'),
                            Section::make('Pièces du dossier')
                                ->columnSpanFull()
                                ->schema([
                                    self::documents('piece_identite'),
                                    self::documents('extrait_naissance'),
                                    self::documents('certificat_residence'),
                                    self::documents('fiche_familiale')
                                        ->visible(fn ($record) => $record->situation_familiale === 'marie'),
                                ]),
                        ]),
 
                    // ───────────── Emploi ─────────────
                    Tabs\Tab::make('Emploi')
                        ->icon('heroicon-o-briefcase')
                        ->columns(2)
                        ->schema([
                            self::text('date_embauche', 'Date d\'entrée')->date('d/m/Y'),
                            self::text('type_contrat', 'Type de contrat'),
                            self::text('departement.nom', 'Département'),
                            self::text('poste', 'Poste occupé'),
                            self::text('echelon', 'Échelon'),
                            self::text('grade', 'Grade'),
                            self::text('classe', 'Classe'),
                            self::text('jours_conges_restant', 'Jours de congés restants')->suffix(' jours'),
                            Section::make('Pièces du dossier')
                                ->columnSpanFull()
                                ->schema([
                                    self::documents('contrat_travail'),
                                ]),
                        ]),
 
                    // ───────────── Sécurité sociale & paiement ─────────────
                    Tabs\Tab::make('Sécurité sociale & paiement')
                        ->icon('heroicon-o-banknotes')
                        ->columns(2)
                        ->schema([
                            self::masked('numero_assurance_sociale', 'N° de sécurité sociale (CNAS)'),
                            self::text('cle_cnas', 'Clé CNAS'),
                            IconEntry::make('conjoint_travaille')
                                ->label('Le conjoint travaille')
                                ->boolean()
                                ->visible(fn ($record) => $record->situation_familiale === 'marie'),
                            self::text('mode_paiement', 'Mode de paiement')
                                ->formatStateUsing(fn (?string $state) => self::MODES_PAIEMENT[$state] ?? $state),
                            self::masked('numero_compte', 'N° de compte (RIP / RIB)'),
                            self::text('code_banque_agence', 'Code banque / agence'),
                        ]),
 
                    // ───────────── Compétences & formations ─────────────
                    Tabs\Tab::make('Compétences & formations')
                        ->icon('heroicon-o-academic-cap')
                        ->columns(2)
                        ->schema([
                            self::text('niveau_etude', 'Niveau d\'étude'),
                            self::text('dernier_diplome', 'Dernier diplôme obtenu'),
                            Section::make('Pièces du dossier')
                                ->columnSpanFull()
                                ->schema([
                                    self::documents('diplome'),
                                ]),
                        ]),
 
                    // ───────────── Compte ─────────────
                    Tabs\Tab::make('Compte')
                        ->icon('heroicon-o-key')
                        ->columns(2)
                        ->schema([
                            self::text('email', 'Email de connexion'),
                            self::badge('role', 'Rôle', self::ROLES),
                            self::badge('status', 'Statut', self::STATUTS),
                            self::text('created_at', 'Compte créé le')->dateTime('d/m/Y H:i'),
                        ]),
                ])
                ->persistTabInQueryString()
                ->columnSpanFull(),
        ]);
    }
 
    private static function text(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)->label($label)->placeholder('—');
    }
 
    // Affiche seulement les 4 derniers caractères (NIN, CNAS, RIB) ; la valeur complète est dans le formulaire de modification
    private static function masked(string $name, string $label): TextEntry
    {
        return self::text($name, $label)->formatStateUsing(
            fn (?string $state) => filled($state)
                ? str_repeat('•', max(strlen($state) - 4, 0)) . substr($state, -4)
                : null
        );
    }
 
    private static function badge(string $name, string $label, array $labels): TextEntry
    {
        return self::text($name, $label)
            ->badge()
            ->formatStateUsing(fn (?string $state) => $labels[$state] ?? $state)
            ->color(fn (?string $state): string => match ($state) {
                'actif' => 'success',
                'inactif' => 'danger',
                'admin', 'rh' => 'primary',
                default => 'gray',
            });
    }
 
    /**
     * Liste des fichiers d'un type de pièce, avec un lien d'ouverture temporaire
     * (les documents sont sur un disque privé).
     */
    private static function documents(string $type): RepeatableEntry
    {
        return RepeatableEntry::make("documents_{$type}")
            ->label(Document::TYPES[$type])
            ->state(fn ($record) => $record->documents->where('type', $type)->values())
            ->placeholder('Non fourni')
            ->columns(2)
            ->schema([
                TextEntry::make('file_name')
                    ->hiddenLabel()
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->url(fn ($record) => Storage::disk(Document::DISK)->temporaryUrl($record->file_path, now()->addMinutes(10)))
                    ->openUrlInNewTab(),
                TextEntry::make('file_size')
                    ->hiddenLabel()
                    ->formatStateUsing(fn ($state) => $state ? Number::fileSize((int) $state) : '—'),
            ])
            ->columnSpanFull();
    }
}