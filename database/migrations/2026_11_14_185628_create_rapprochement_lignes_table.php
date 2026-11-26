<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Comparaison A / B par équipement, figée quand les deux comptages sont terminés, + décision de la direction
        Schema::create('rapprochement_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')->constrained('inventaires')->cascadeOnDelete();
            $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
            $table->boolean('attendu')->default(true);

            $table->boolean('a_trouve')->default(false);
            $table->foreignId('a_emplacement_id')->nullable()->constrained('emplacements')->nullOnDelete();
            $table->string('a_etat', 20)->nullable();
            $table->boolean('b_trouve')->default(false);
            $table->foreignId('b_emplacement_id')->nullable()->constrained('emplacements')->nullOnDelete();
            $table->string('b_etat', 20)->nullable();

            // concordant | ecart_emplacement | ecart_etat | ecart_les_deux | un_seul | introuvable
            $table->string('concordance', 20);

            // Décision de la direction (null = reste à trancher)
            $table->string('decision', 20)->nullable();                   // retrouve | non_retrouve
            $table->foreignId('emplacement_final_id')->nullable()->constrained('emplacements')->nullOnDelete();
            $table->string('etat_final', 20)->nullable();
            $table->text('action')->nullable();
            $table->foreignId('decide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['inventaire_id', 'equipement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapprochement_lignes');
    }
};