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
        Schema::create('ligne_inventaires', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventaire_id')
                ->constrained('inventaires')
                ->cascadeOnDelete();

            $table->foreignId('equipement_id')
                ->constrained('equipements')
                ->restrictOnDelete();

            $table->foreignId('emplacement_constate_id')
                ->nullable()
                ->constrained('emplacements')
                ->nullOnDelete();

            $table->string('etat_constate')->nullable();

            $table->dateTime('scanne_le')->nullable();

            $table->foreignId('scanne_par')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('remarque')->nullable();

            $table->timestamps();

            $table->unique(
                ['inventaire_id', 'equipement_id'],
                'inventaire_equipement_unique'
            );

            $table->index('scanne_le');
            $table->index('emplacement_constate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_inventaires');
    }
};
