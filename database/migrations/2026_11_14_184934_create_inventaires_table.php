<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Récolement parent : périmètre + deux commissions indépendantes + rapprochement par la direction
        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('statut', 20)->default('comptage');            // comptage | rapprochement | cloture
            $table->foreignId('emplacement_id')->nullable()->constrained('emplacements')->nullOnDelete(); // périmètre (null = tout)
            $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            // Références et conclusions du PV final
            $table->string('decision_numero', 50)->nullable();            // décision de nomination des commissions
            $table->date('decision_date')->nullable();
            $table->string('resultat_rapprochement', 20)->nullable();     // total | limite | substantiel
            $table->text('details_rapprochement')->nullable();
            $table->string('visa_directeur', 20)->nullable();             // adopte | complement | troisieme
            $table->text('observations_directeur')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaires');
    }
};
