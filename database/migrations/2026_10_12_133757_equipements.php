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
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();
 
            // Identification : une étiquette code-barres par unité
            $table->string('code_barre', 50)->nullable()->unique(); // généré à la création, ex: INF-2026-000123
            $table->string('numero_inventaire', 50)->nullable()->unique(); // n° d'inventaire officiel (secteur public)
 
            // Description
            $table->string('designation');
            $table->foreignId('categorie_id')->constrained('categorie_equipements')->restrictOnDelete();
            $table->string('marque')->nullable();
            $table->string('modele')->nullable();
            $table->string('numero_serie')->nullable()->index();
 
            // Situation actuelle
            $table->string('etat', 20)->default('bon');         // neuf | bon | use | degrade | hors_service
            $table->string('statut', 20)->default('en_stock')->index(); // en_stock | affecte | en_maintenance | reforme | perdu
            $table->foreignId('emplacement_id')->nullable()->constrained('emplacements')->nullOnDelete();
 
            // Acquisition
            $table->date('date_acquisition')->nullable();
            $table->decimal('prix_acquisition', 12, 2)->nullable();
            $table->string('reference_facture', 100)->nullable(); // n° de facture ou bon de livraison (texte libre)
            $table->date('date_fin_garantie')->nullable();
 
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};
