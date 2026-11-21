<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une ligne par équipement ET par commission : ce que CETTE commission a constaté.
        // scanne_le = null : pas retrouvé par la commission.
        Schema::create('ligne_inventaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')->constrained('inventaires')->cascadeOnDelete();
            $table->foreignId('commission_id')->constrained('commissions_inventaire')->cascadeOnDelete();
            $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();

            // Photo du patrimoine à l'ouverture (les PV restent reproductibles après changement d'emplacement)
            $table->foreignId('emplacement_attendu_id')->nullable()->constrained('emplacements')->nullOnDelete();
            $table->string('etat_attendu', 20)->nullable();
            $table->boolean('hors_perimetre')->default(false);            // scanné alors qu'il n'était pas attendu

            // Constat de la commission
            $table->foreignId('emplacement_constate_id')->nullable()->constrained('emplacements')->nullOnDelete();
            $table->string('etat_constate', 20)->nullable();
            $table->dateTime('scanne_le')->nullable();
            $table->foreignId('scanne_par')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarque')->nullable();
            $table->timestamps();

            $table->unique(['commission_id', 'equipement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_inventaires');
    }
};