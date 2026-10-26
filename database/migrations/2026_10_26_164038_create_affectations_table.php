<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Historique des affectations : une ligne par période de détention.
        // date_fin = null signifie "affectation en cours".
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
            $table->foreignId('employe_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('departement_id')->nullable()->constrained('departements')->nullOnDelete(); // bien partagé (ex: imprimante)
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
 
            $table->index(['equipement_id', 'date_fin']);
            $table->index(['employe_id', 'date_fin']);
        });
 
        // PostgreSQL : un équipement ne peut avoir qu'une seule affectation en cours
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX affectations_une_seule_en_cours ON affectations (equipement_id) WHERE date_fin IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
