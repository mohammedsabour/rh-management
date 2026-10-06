<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // ID_Employe
 
            // --- Bloc identité & état civil ---
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance')->nullable();
            $table->string('wilaya_naissance')->nullable();
            $table->string('commune_naissance')->nullable();
            $table->string('sexe', 10)->nullable();                       // masculin | feminin
            $table->string('situation_familiale', 20)->nullable();        // celibataire | marie | divorce | veuf
            $table->text('nin')->nullable();                              // chiffré (cast 'encrypted')
            $table->unsignedTinyInteger('nombre_enfants')->default(0);
            $table->unsignedTinyInteger('nombre_enfants_a_charge')->default(0);
            $table->string('situation_service_national', 20)->nullable(); // degage | incorpore | exempte | sursitaire
            $table->string('adresse')->nullable();
            $table->string('wilaya_residence')->nullable();
            $table->string('commune_residence')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('email')->unique();                            // identifiant de connexion
            $table->string('email_personnel')->nullable();
            $table->string('photo')->nullable(); 
 
            // --- Bloc contractuel & statutaire ---
            $table->date('date_embauche')->nullable();                    // date d'entrée
            $table->string('type_contrat', 10)->nullable();               // CDI | CDD | CTA
            $table->foreignId('departement_id')->nullable()
                ->constrained('departements')->nullOnDelete();
            $table->string('poste')->nullable();
            $table->string('echelon')->nullable();
            $table->string('grade')->nullable();
            $table->string('classe')->nullable();
 
            // --- Bloc sécurité sociale & paiement ---
            $table->text('numero_assurance_sociale')->nullable();         // CNAS, chiffré
            $table->string('cle_cnas', 2)->nullable();
            $table->boolean('conjoint_travaille')->nullable();
            $table->string('mode_paiement', 20)->nullable();              // virement_ccp | virement_bancaire
            $table->text('numero_compte')->nullable();                    // RIP/RIB, chiffré
            $table->string('code_banque_agence')->nullable();
 
            // --- Bloc compétences & formations ---
            $table->string('niveau_etude')->nullable();
            $table->string('dernier_diplome')->nullable();

            // --- Bloc congé ---
            $table->unsignedInteger('joures_conges_restant')->default(18);
 
            // --- Compte applicatif ---
            $table->string('status', 20)->default('actif');
            $table->string('role', 20)->default('employe');               // admin | rh | employe
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
