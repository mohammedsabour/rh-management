<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Concerns\DossierEmploye;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;


class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, DossierEmploye;

    public const SEXES = ['masculin', 'feminin'];
    public const SITUATIONS_FAMILIALES = ['celibataire', 'marie', 'divorce', 'veuf'];
    public const SITUATIONS_SERVICE_NATIONAL = ['degage', 'incorpore', 'exempte', 'sursitaire'];
    public const TYPES_CONTRAT = ['CDI', 'CDD', 'CTA'];
    public const MODES_PAIEMENT = ['virement_ccp', 'virement_bancaire', 'cheque', 'espece'];
    public const MOTIFS_DEPART = ['retraite' => 'Retraite', 'demission' => 'Démission'];

    protected $fillable = [
        // Identité & état civil
        'nom',
        'nom_ar',
        'prenom',
        'prenom_ar',
        'date_naissance',
        'wilaya_naissance',
        'commune_naissance',
        'sexe',
        'situation_familiale',
        'nin',
        'nombre_enfants',
        'nombre_enfants_a_charge',
        'situation_service_national',
        'adresse',
        'wilaya_residence',
        'commune_residence',
        'telephone',
        'email',
        'email_personnel',
        'photo',
 
        // Contractuel & statutaire
        'date_embauche',
        'type_contrat',
        'departement_id',
        'poste',
        'echelon',
        'grade',
        'classe',
 
        // Sécurité sociale & paiement
        'numero_assurance_sociale',
        'cle_cnas',
        'conjoint_travaille',
        'mode_paiement',
        'numero_compte',
        'code_banque_agence',

        // Compétences & formations
        'niveau_etude',
        'dernier_diplome',
 
        // Compte applicatif
        'is_active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
        protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_embauche' => 'date:Y-m-d',
            'date_naissance' => 'date:Y-m-d',
            'conjoint_travaille' => 'boolean',
            'nombre_enfants' => 'integer',
            'nombre_enfants_a_charge' => 'integer',
            'date_fin' => 'date:Y-m-d',
        ];
    }

    public function getFilamentName(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }
 
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return (bool) $this->is_active;
    }

    protected static function booted(): void
    {
        static::created(fn (self $user) => $user->assignRole('employe'));
    }
    
    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'employe_id');
    }
 
    public function absences()
    {
        return $this->hasMany(Absence::class, 'employe_id');
    }

    public function demandeConges()
    {
        return $this->hasMany(DemandeConge::class, 'employe_id');
    }
}